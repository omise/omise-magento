<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Omise\Payment\Model\Config\Installment as OmiseInstallmentConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;

class InstallmentConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Magento\Payment\Api\PaymentMethodListInterface;
     */
    private $_paymentLists;
    private $_scopeConfig;

    public function __construct(
        PaymentMethodListInterface $paymentLists,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->_paymentLists = $paymentLists;
        $this->_scopeConfig  = $scopeConfig;
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
                    'installment_config' => [
                        OmiseInstallmentConfig::KBANK => $this->scopeConfig->getValue('payment/' . OmiseInstallmentConfig::KBANK . '/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                    ]
                ];
            }
        }
        return [];
    }
}
