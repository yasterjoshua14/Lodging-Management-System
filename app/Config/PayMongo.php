<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class PayMongo extends BaseConfig
{
    public string $secretKey = '';

    public string $baseUri = 'https://api.paymongo.com/v1/';

    public string $merchantName = 'Lodging Management System';

    public bool $sendEmailReceipt = false;

    /**
     * @var list<string>
     */
    public array $paymentMethodTypes = ['gcash', 'paymaya', 'card'];

    public function __construct()
    {
        parent::__construct();

        $secretKey = $this->firstEnvironmentValue('PAYMONGO_SECRET_KEY', 'paymongo.secretKey');
        if (is_string($secretKey)) {
            $this->secretKey = trim($secretKey);
        }

        $baseUri = $this->firstEnvironmentValue('PAYMONGO_BASE_URI', 'paymongo.baseUri');
        if (is_string($baseUri) && trim($baseUri) !== '') {
            $this->baseUri = trim($baseUri);
        }

        $merchantName = $this->firstEnvironmentValue('PAYMONGO_MERCHANT_NAME', 'paymongo.merchantName');
        if (is_string($merchantName) && trim($merchantName) !== '') {
            $this->merchantName = trim($merchantName);
        }

        $paymentMethodTypes = $this->firstEnvironmentValue('PAYMONGO_PAYMENT_METHOD_TYPES', 'paymongo.paymentMethodTypes');
        if (is_string($paymentMethodTypes) && trim($paymentMethodTypes) !== '') {
            $this->paymentMethodTypes = $this->parsePaymentMethodTypes($paymentMethodTypes);
        }

        $sendEmailReceipt = $this->firstEnvironmentValue('PAYMONGO_SEND_EMAIL_RECEIPT', 'paymongo.sendEmailReceipt');
        if ($sendEmailReceipt !== null) {
            $this->sendEmailReceipt = filter_var($sendEmailReceipt, FILTER_VALIDATE_BOOL);
        }
    }

    /**
     * @return list<string>
     */
    private function parsePaymentMethodTypes(string $value): array
    {
        $methods = array_values(array_filter(
            array_map(
                static fn (string $method): string => trim($method),
                explode(',', $value)
            ),
            static fn (string $method): bool => $method !== ''
        ));

        return $methods !== [] ? $methods : $this->paymentMethodTypes;
    }

    private function firstEnvironmentValue(string ...$names): ?string
    {
        foreach ($names as $name) {
            $value = getenv($name);
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }

            $value = env($name);
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }
}
