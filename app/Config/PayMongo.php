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

        $secretKey = env('paymongo.secretKey');
        if (is_string($secretKey)) {
            $this->secretKey = trim($secretKey);
        }

        $baseUri = env('paymongo.baseUri');
        if (is_string($baseUri) && trim($baseUri) !== '') {
            $this->baseUri = trim($baseUri);
        }

        $merchantName = env('paymongo.merchantName');
        if (is_string($merchantName) && trim($merchantName) !== '') {
            $this->merchantName = trim($merchantName);
        }

        $paymentMethodTypes = env('paymongo.paymentMethodTypes');
        if (is_string($paymentMethodTypes) && trim($paymentMethodTypes) !== '') {
            $this->paymentMethodTypes = $this->parsePaymentMethodTypes($paymentMethodTypes);
        }

        $sendEmailReceipt = env('paymongo.sendEmailReceipt');
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
}
