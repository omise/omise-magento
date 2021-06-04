<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Omise\Payment\Model\Capabilities;
use Omise\Payment\Model\Config\Fpx;
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
                    $configs['installment_backends'] = $this->capabilities->retrieveInstallmentBackends();
                    $configs['is_zero_interest'] = $this->capabilities->isZeroInterest();
                    break;
                case  Fpx::CODE :
                    $backendsFpx = $this->capabilities->getBackendsByType(Fpx::TYPE);
                    $configs['fpx']['banks'] = $backendsFpx ? current($backendsFpx)->banks : [];
                    break;
            }
        }



        return $configs;
    }
}
