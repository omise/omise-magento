<?php

namespace Omise\Payment\Model\Ui;

use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Capabilities;
use Omise\Payment\Model\Config\Shopeepay;
use Omise\Payment\Model\Config\CcGooglePay;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Omise\Payment\Model\Config\Installment as OmiseInstallmentConfig;

class CapabilitiesConfigProvider implements ConfigProviderInterface
{
    private $_storeManager;

    private $capabilities;

    private $helper;

    /**
     * @var Magento\Payment\Api\PaymentMethodListInterface;
     */
    private $_paymentLists;

    public function __construct(
        Capabilities               $capabilities,
        PaymentMethodListInterface $paymentLists,
        StoreManagerInterface      $storeManager,
        OmiseHelper $helper
    ) {
        $this->capabilities    = $capabilities;
        $this->_paymentLists   = $paymentLists;
        $this->_storeManager   = $storeManager;
        $this->helper = $helper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $listOfActivePaymentMethods = $this->_paymentLists->getActiveList($this->_storeManager->getStore()->getId());
        $configs = [];

        // Retrieve available backends & methods from capabilities api
        $backends = $this->capabilities->getBackendsWithOmiseCode();
        $tokenization_methods = $this->capabilities->getTokenizationMethodsWithOmiseCode();
        $backends = array_merge($backends, $tokenization_methods);
        $configs['omise_installment_limit_amount'] = $this->capabilities->getInstallmentLimitAmount();

        foreach ($listOfActivePaymentMethods as $method) {
            $code = $method->getCode();

            if ($code === OmiseInstallmentConfig::CODE) {
                $configs['is_zero_interest'] = $this->capabilities->isZeroInterest();
            } elseif ($code === CcGooglePay::CODE) {
                $configs['card_brands'] = $this->capabilities->getCardBrands();
            }

            // filter only active backends
            if (array_key_exists($code, $backends)) {
                if ($code === 'omise_offsite_shopeepay') {
                    $configs['omise_payment_list'][$code] = $this->getShopeeBackendByType($backends[$code]);
                } else {
                    $configs['omise_payment_list'][$code] = $backends[$code];
                }
            }
        }

        return $configs;
    }

    /**
     * Return the right ShopeePay backend depending on the platform and availability of
     * the backend in the capability
     */
    private function getShopeeBackendByType($shopeeBackends)
    {
        $jumpAppBackend = [];
        $mpmBackend = [];

        // Since ShopeePay will have two types i.e shopeepay and shopeepay_jumpapp,
        // we split and store the type in separate variables.
        foreach ($shopeeBackends as $backend) {
            if ($backend->type === Shopeepay::JUMPAPP_ID) {
                $jumpAppBackend[] = $backend;
            } else {
                $mpmBackend[] = $backend;
            }
        }

        $isShopeepayJumpAppEnabled = $this->capabilities->isBackendEnabled(Shopeepay::JUMPAPP_ID);
        $isShopeepayEnabled = $this->capabilities->isBackendEnabled(Shopeepay::ID);

        // If user is in mobile and jump app is enabled then return jumpapp backend
        if ($this->helper->isMobilePlatform() && $isShopeepayJumpAppEnabled) {
            return $jumpAppBackend;
        }

        // If above condition fails then it means either
        //
        // Case 1.
        // User is using mobile device but jump app is not enabled.
        // This means shopeepay direct is enabled otherwise this code would not execute.
        //
        // Case 2.
        // Jump app is enabled but user is not using mobile device
        //
        // In both cases we will want to show the shopeepay MPM backend first if MPM is enabled.
        // If MPM is not enabled then it means jump app is enabled because this code would never
        // execute if none of the shopee backends were disabled.
        return $isShopeepayEnabled ? $mpmBackend : $jumpAppBackend;
    }
}
