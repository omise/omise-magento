<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Omise\Payment\Model\Config\Installment as OmiseInstallmentConfig;

class InstallmentConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Magento\Payment\Api\PaymentMethodListInterface;
     */
    private $_paymentLists;

    public function __construct(
        PaymentMethodListInterface $paymentLists
    )
    {
        $this->_paymentLists   = $paymentLists;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $listOfActivePaymentMethods = $this->_paymentLists->getActiveList($this->_storeManager->getStore()->getId());
        foreach ($listOfActivePaymentMethods as $method) {
            if ($method->getCode() === OmiseInstallmentConfig::CODE) {
                return [ 
                ];
            }
        }
        return [];
    }
}
