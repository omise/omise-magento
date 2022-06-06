<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Model\Config\CcGooglePay;

class PluginConfigProvider implements ConfigProviderInterface
{
    private $config;
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'isOmiseSandboxOn' => $this->config->isSandboxEnabled(),
            CcGooglePay::CODE => [
                'merchantId' => $this->config->getValue('merchant_id', CcGooglePay::CODE),
                'requestBillingAddress' => $this->config->getValue('request_billing_address', CcGooglePay::CODE),
                'requestPhoneNumber' => $this->config->getValue('request_phone_number', CcGooglePay::CODE)
            ]
        ];
    }
}
