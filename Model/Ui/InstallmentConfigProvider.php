<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Omise\Payment\Model\Config\Installment as OmiseInstallmentConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Payment\Api\PaymentMethodListInterface;

class InstallmentConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Magento\Payment\Api\PaymentMethodListInterface
     */
    private $_paymentLists;

    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    public function __construct(
        PaymentMethodListInterface $paymentLists,
        ScopeConfigInterface       $scopeConfig,
        StoreManagerInterface      $storeManager
    )
    {
        $this->_paymentLists = $paymentLists;
        $this->_scopeConfig  = $scopeConfig;
        $this->_storeManager = $storeManager;
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
                        OmiseInstallmentConfig::KBANK => $this->_scopeConfig->getValue('installment_config/' . OmiseInstallmentConfig::KBANK . '/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                        OmiseInstallmentConfig::BBL => $this->_scopeConfig->getValue('installment_config/' . OmiseInstallmentConfig::BBL . '/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                        OmiseInstallmentConfig::KRUNGTHAI => $this->_scopeConfig->getValue('installment_config/' . OmiseInstallmentConfig::KRUNGTHAI . '/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                        OmiseInstallmentConfig::FIRSTCHOICE => $this->_scopeConfig->getValue('installment_config/' . OmiseInstallmentConfig::FIRSTCHOICE . '/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                        OmiseInstallmentConfig::KRUNGSRI => $this->_scopeConfig->getValue('installment_config/' . OmiseInstallmentConfig::KRUNGSRI . '/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                    ]
                ];
            }
        }
        return [];
    }
}
