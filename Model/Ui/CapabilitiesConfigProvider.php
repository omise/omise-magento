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
use Psr\Log\LoggerInterface;

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
        LoggerInterface $logger
    ) {
        $this->capabilities    = $capabilities;
        $this->_paymentLists   = $paymentLists;
        $this->_storeManager   = $storeManager;
        $this->logger = $logger;
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

        // $this->logger->debug(print_r($listOfActivePaymentMethods, true));
        // $this->logger->debug(print_r($backends, true));

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
                $configs['omise_payment_list'][$code]= $backends[$code];
            }
        }
        return $configs;
    }
}
