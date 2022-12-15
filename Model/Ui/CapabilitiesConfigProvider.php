<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Omise\Payment\Model\Capabilities;
use Omise\Payment\Model\Config\CcGooglePay;
use Omise\Payment\Model\Config\Fpx;
use Omise\Payment\Model\Config\Internetbanking;
use Omise\Payment\Model\Config\Mobilebanking;
use Omise\Payment\Model\Config\Shopeepay;
use Omise\Payment\Model\Config\Installment as OmiseInstallmentConfig;
use Omise\Payment\Helper\OmiseHelper;

class CapabilitiesConfigProvider implements ConfigProviderInterface
{
    private $_storeManager;

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

        foreach ($listOfActivePaymentMethods as $method) {
            $code = $method->getCode();

            switch ($code) {
                case OmiseInstallmentConfig::CODE:
                    $configs['is_zero_interest'] = $this->capabilities->isZeroInterest();
                    break;
                case CcGooglePay::CODE:
                    $configs['card_brands'] = $this->capabilities->getCardBrands();
                    break;
            }

            // filter only active backends
            if (array_key_exists($code, $backends)) {
                if ($code === 'omise_offsite_shopeepay') {
                    $configs['omise_payment_list'][$code] = $this->getShopeeBackendByType($backends[$code]);
                } else {
                    $configs['omise_payment_list'][$code]= $backends[$code];
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

        if ($this->helper->isMobilePlatform() && $isShopeepayJumpAppEnabled) {
            return $jumpAppBackend;
        }

        return $isShopeepayEnabled ? $mpmBackend : $jumpAppBackend;
    }
}
