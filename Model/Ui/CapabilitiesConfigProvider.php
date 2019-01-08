<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Omise\Payment\Model\Capabilities;
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
     * Checks if Installment payment method is enabled
     * If it is, than it download capabilities thorough Capability Model,
     * Otherwise returns empty array
     *
     * @return array
     */
    private function getCapabilities()
    {
        $listOfActivePaymentMethods = $this->_paymentLists->getActiveList($this->_storeManager->getStore()->getId());
        foreach ($listOfActivePaymentMethods as $method) {
            if ($method->getCode() === OmiseInstallmentConfig::CODE) {
                return $this->capabilities->get();
            }
        }
        return [];
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'capabilities' => $this->getCapabilities(),
        ];
    }
}
