<?php

namespace App\Libraries;

use Config\PayMongo;
use Config\Services;
use RuntimeException;

class PayMongoCheckout
{
    private PayMongo $config;

    public function __construct(?PayMongo $config = null)
    {
        $this->config = $config ?? config('PayMongo');
    }

    public function isConfigured(): bool
    {
        return trim($this->config->secretKey) !== '';
    }

    /**
     * @param array<string, mixed> $booking
     * @param array<string, mixed> $room
     * @param array<string, mixed>|null $tenant
     *
     * @return array<string, mixed>
     */
    public function createCheckoutSession(array $booking, array $room, ?array $tenant = null): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('PayMongo is not configured. Add your PayMongo secret key first.');
        }

        $roomNumber      = (string) ($room['room_number'] ?? 'Room');
        $roomType        = room_type_options()[$room['type'] ?? ''] ?? humanize_key($room['type'] ?? null);
        $durationLabel   = hour_duration_label($room['pricing_hours'] ?? 1);
        $referenceNumber = 'BOOKING-' . (int) $booking['id'] . '-' . date('YmdHis');
        $successUrl      = site_url('myBookings/payment/success') . '?booking=' . (int) $booking['id'];
        $cancelUrl       = site_url('myBookings/payment/cancel') . '?booking=' . (int) $booking['id'];

        $attributes = [
            'cancel_url'           => $cancelUrl,
            'description'          => sprintf('Booking for Room %s (%s)', $roomNumber, $durationLabel),
            'line_items'           => [[
                'amount'      => $this->toCentavos($room['price_per_night'] ?? 0),
                'currency'    => 'PHP',
                'description' => trim((string) ($room['description'] ?? '')) ?: 'Room booking',
                'name'        => sprintf('Room %s (%s, %s)', $roomNumber, $roomType, $durationLabel),
                'quantity'    => 1,
            ]],
            'merchant'             => $this->config->merchantName,
            'metadata'             => [
                'booking_id'     => (string) ($booking['id'] ?? ''),
                'pricing_hours'  => (string) ($room['pricing_hours'] ?? 1),
                'room_id'        => (string) ($booking['room_id'] ?? ''),
                'tenant_id'      => (string) ($booking['tenant_id'] ?? ''),
            ],
            'payment_method_types' => $this->config->paymentMethodTypes,
            'reference_number'     => $referenceNumber,
            'send_email_receipt'   => $this->config->sendEmailReceipt,
            'show_description'     => true,
            'show_line_items'      => true,
            'success_url'          => $successUrl,
        ];

        $billing = array_filter([
            'email' => trim((string) ($tenant['email'] ?? '')),
            'name'  => trim((string) ($tenant['full_name'] ?? '')),
            'phone' => trim((string) ($tenant['phone'] ?? '')),
        ], static fn (string $value): bool => $value !== '');

        if ($billing !== []) {
            $attributes['billing'] = $billing;
        }

        $payload = $this->request('POST', 'checkout_sessions', [
            'data' => [
                'attributes' => $attributes,
            ],
        ]);

        $responseAttributes = $this->sessionAttributes($payload);
        $sessionId          = (string) ($payload['data']['id'] ?? '');
        $checkoutUrl        = (string) ($responseAttributes['checkout_url'] ?? '');

        if ($sessionId === '' || $checkoutUrl === '') {
            throw new RuntimeException('PayMongo returned an incomplete checkout session response.');
        }

        return [
            'checkout_url'     => $checkoutUrl,
            'raw'              => $payload,
            'reference_number' => (string) ($responseAttributes['reference_number'] ?? $referenceNumber),
            'session_id'       => $sessionId,
            'status'           => $this->getStatus($payload),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveCheckoutSession(string $sessionId): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('PayMongo is not configured. Add your PayMongo secret key first.');
        }

        return $this->request('GET', 'checkout_sessions/' . rawurlencode($sessionId));
    }

    /**
     * @param array<string, mixed> $session
     */
    public function isPaid(array $session): bool
    {
        $attributes = $this->sessionAttributes($session);
        $status     = strtolower((string) ($attributes['status'] ?? ''));

        if ($status === 'paid') {
            return true;
        }

        $paymentIntentStatus = strtolower((string) ($attributes['payment_intent']['attributes']['status'] ?? ''));
        if (in_array($paymentIntentStatus, ['paid', 'succeeded'], true)) {
            return true;
        }

        foreach ($this->extractPayments($attributes) as $payment) {
            $paymentAttributes = is_array($payment['attributes'] ?? null) ? $payment['attributes'] : [];
            $paymentStatus     = strtolower((string) ($paymentAttributes['status'] ?? ''));

            if ($paymentStatus === 'paid' || ! empty($paymentAttributes['paid_at'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $session
     */
    public function getStatus(array $session): string
    {
        $attributes = $this->sessionAttributes($session);
        $status     = trim((string) ($attributes['status'] ?? ''));

        if ($status !== '') {
            return strtolower($status);
        }

        $paymentIntentStatus = trim((string) ($attributes['payment_intent']['attributes']['status'] ?? ''));
        if ($paymentIntentStatus !== '') {
            return strtolower($paymentIntentStatus);
        }

        foreach ($this->extractPayments($attributes) as $payment) {
            $paymentAttributes = is_array($payment['attributes'] ?? null) ? $payment['attributes'] : [];
            $paymentStatus     = trim((string) ($paymentAttributes['status'] ?? ''));

            if ($paymentStatus !== '') {
                return strtolower($paymentStatus);
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $session
     */
    public function getReferenceNumber(array $session): string
    {
        return trim((string) ($this->sessionAttributes($session)['reference_number'] ?? ''));
    }

    /**
     * @param array<string, mixed> $session
     */
    public function getPaidAt(array $session): ?string
    {
        foreach ($this->extractPayments($this->sessionAttributes($session)) as $payment) {
            $paymentAttributes = is_array($payment['attributes'] ?? null) ? $payment['attributes'] : [];
            $paidAt            = $paymentAttributes['paid_at'] ?? null;

            if (is_int($paidAt) || ctype_digit((string) $paidAt)) {
                return gmdate('Y-m-d H:i:s', (int) $paidAt);
            }

            if (is_string($paidAt) && trim($paidAt) !== '') {
                return trim($paidAt);
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed>|null $body
     *
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, ?array $body = null): array
    {
        $client = Services::curlrequest([
            'baseURI'     => rtrim($this->config->baseUri, '/') . '/',
            'http_errors' => false,
            'timeout'     => 30,
        ], null, null, false);

        $options = [
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->config->secretKey . ':'),
            ],
        ];

        if ($body !== null) {
            $options['json'] = $body;
        }

        $response   = $client->request($method, ltrim($path, '/'), $options);
        $statusCode = $response->getStatusCode();
        $payload    = json_decode($response->getBody(), true);

        if (! is_array($payload)) {
            throw new RuntimeException('PayMongo returned an unreadable response.');
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException($this->extractErrorMessage($payload));
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function sessionAttributes(array $payload): array
    {
        return is_array($payload['data']['attributes'] ?? null) ? $payload['data']['attributes'] : [];
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return list<array<string, mixed>>
     */
    private function extractPayments(array $attributes): array
    {
        $payments = $attributes['payments'] ?? $attributes['payment_intent']['attributes']['payments'] ?? [];

        return is_array($payments) ? array_values(array_filter($payments, 'is_array')) : [];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractErrorMessage(array $payload): string
    {
        $errors = $payload['errors'] ?? [];

        if (is_array($errors) && $errors !== []) {
            $firstError = $errors[0] ?? [];
            $detail     = trim((string) ($firstError['detail'] ?? ''));
            if ($detail !== '') {
                return $detail;
            }

            $code = trim((string) ($firstError['code'] ?? ''));
            if ($code !== '') {
                return 'PayMongo request failed: ' . $code;
            }
        }

        return 'PayMongo request failed. Please verify your API keys and enabled payment methods.';
    }

    /**
     * @param float|int|string|null $amount
     */
    private function toCentavos($amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
