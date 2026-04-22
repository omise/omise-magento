<?php

namespace Omise\Payment\Model\Ui;

use Omise\Payment\Helper\RequestHelper;
use Omise\Payment\Model\Capability;
use Omise\Payment\Model\Config\Shopeepay;
use Omise\Payment\Model\Config\Truemoney;
use Omise\Payment\Model\Config\CcGooglePay;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Omise\Payment\Model\Config\Installment as OmiseInstallmentConfig;
use Omise\Payment\Model\Config\Config;

class CapabilityConfigProvider implements ConfigProviderInterface
{
    private $_storeManager;

    private $capability;

    /**
     * @var \Omise\Payment\Helper\RequestHelper
     */
    private $requestHelper;

    /**
     * @var Magento\Payment\Api\PaymentMethodListInterface;
     */
    private $_paymentLists;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Capability               $capability,
        PaymentMethodListInterface $paymentLists,
        StoreManagerInterface      $storeManager,
        RequestHelper $requestHelper,
        Config $config
    ) {
        $this->capability    = $capability;
        $this->_paymentLists   = $paymentLists;
        $this->_storeManager   = $storeManager;
        $this->requestHelper = $requestHelper;
        $this->config = $config;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $listOfActivePaymentMethods = $this->_paymentLists->getActiveList($this->_storeManager->getStore()->getId());
        $currency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $configs = [];
        $configs['omise_installment_min_limit'] = $this->capability->getInstallmentMinLimit($currency);
        $configs['omise_payment_list'] = [];

        foreach ($listOfActivePaymentMethods as $method) {
            $code = $method->getCode();

            if ($code === OmiseInstallmentConfig::CODE) {
                $configs['is_zero_interest'] = $this->capability->isZeroInterest();
            } elseif ($code === CcGooglePay::CODE) {
                $configs['card_brands'] = $this->capability->getCardBrands();
            }

            $this->filterActiveBackends($code, $configs['omise_payment_list']);
        }

        $configs['omise_wlb_enable'] = $this->checkWlbStatus($configs['omise_payment_list']);
        $configs['omise_upa_feature'] = $this->config->getIsUpaFeatureFlagEnabled();
        return $configs;
    }

    /**
     * Check the Wlb active or not
     * @var array
     * @return int
     */
    private function checkWlbStatus($omise_payment_list){
        if(array_key_exists('omise_offsite_installment',$omise_payment_list)){
            foreach ($omise_payment_list['omise_offsite_installment'] as $method) {
                if (str_starts_with($method->name, 'installment_wlb')) {
                    return 1;
                }
            }
        }
        return 0;
    }

    /**
     * filter only active backends
     * @param $code         Payment method code
     * @param $paymentList  Reference of the payment list
     */
    private function filterActiveBackends($code, &$paymentList)
    {
        // Retrieve available backends & methods from capability api
        $paymentBackends = $this->capability->getBackendsWithOmiseCode();
        $tokenizationMethods = $this->capability->getTokenizationMethodsWithOmiseCode();
        $mergedBackends = array_merge($paymentBackends, $tokenizationMethods);

        // filter only active backends
        if (!array_key_exists($code, $mergedBackends)) {
            return;
        }

        if ($code === Shopeepay::CODE) {
            $backend = $this->getShopeeBackendByType($mergedBackends[$code]);
        } elseif ($code === Truemoney::CODE) {
            $backend = $this->getTruemoneyBackendByType($mergedBackends[$code]);
        } else {
            $backend = $configs['omise_payment_list'][$code] = $mergedBackends[$code];
        }

        $paymentList[$code] = $backend;
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
            if ($backend->name === Shopeepay::JUMPAPP_ID) {
                $jumpAppBackend[] = $backend;
            } else {
                $mpmBackend[] = $backend;
            }
        }

        $isShopeepayJumpAppEnabled = $this->capability->isBackendEnabled(Shopeepay::JUMPAPP_ID);
        $isShopeepayEnabled = $this->capability->isBackendEnabled(Shopeepay::ID);

        // If user is in mobile and jump app is enabled then return jumpapp backend
        if ($this->requestHelper->isMobilePlatform() && $isShopeepayJumpAppEnabled) {
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

    private function getTruemoneyBackendByType($truemoneyBackends)
    {
        $jumpAppBackend = [];
        $walletBackend = [];

        // Since Truemoney will have two types i.e truemoney and truemoney_jumpapp,
        // we split and store the type in separate variables.
        foreach ($truemoneyBackends as $backend) {
            if ($backend->name === Truemoney::JUMPAPP_ID) {
                $jumpAppBackend[] = $backend;
            } else {
                $walletBackend[] = $backend;
            }
        }

        $isJumpAppEnabled = $this->capability->isBackendEnabled(Truemoney::JUMPAPP_ID);
        $isWalletEnabled = $this->capability->isBackendEnabled(Truemoney::ID);

        if (!$isJumpAppEnabled && $isWalletEnabled) {
            return $walletBackend;
        }

        return $jumpAppBackend;
    }
}
