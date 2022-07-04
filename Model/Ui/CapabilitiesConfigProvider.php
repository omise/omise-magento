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
use Omise\Payment\Model\Config\Installment as OmiseInstallmentConfig;

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
        StoreManagerInterface      $storeManager
    ) {
        $this->capabilities    = $capabilities;
        $this->_paymentLists   = $paymentLists;
        $this->_storeManager   = $storeManager;
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
        foreach ($listOfActivePaymentMethods as $method) {
            switch ($method->getCode()) {
                case OmiseInstallmentConfig::CODE:
                    $configs['is_zero_interest'] = $this->capabilities->isZeroInterest();
                    break;
                case CcGooglePay::CODE:
                    $configs['card_brands'] = $this->capabilities->getCardBrands();
                    break;
            }
        }

        $configs['omise_payment_list'] = $this->capabilities->getBackendsWithOmiseCode();
        return $configs;
    }
}
