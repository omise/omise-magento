<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Payment\Model\CcConfig as MagentoCcConfig;
use Magento\Store\Model\StoreManagerInterface;
use Omise\Payment\Model\Capabilities;
use Omise\Payment\Model\Config\Cc as OmiseCcConfig;
use Omise\Payment\Model\Config\Installment as OmiseInstallmentConfig;
use Omise\Payment\Model\Customer;

class OmiseConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Payment\Model\CcConfig
     */
    protected $magentoCcConfig;

    /**
     * @var \Omise\Payment\Model\Config\Cc
     */
    protected $omiseCcConfig;

    /**
     * @var Omise\Payment\Model\Customer
     */
    protected $customer;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var Magento\Payment\Api\PaymentMethodListInterface;
     */
    private $_paymentLists;

    public function __construct(
        MagentoCcConfig            $magentoCcConfig,
        OmiseCcConfig              $omiseCcConfig,
        Customer                   $customer,
        Capabilities               $capabilities,
        PaymentMethodListInterface $paymentLists,
        StoreManagerInterface      $storeManager
    ) {
        $this->magentoCcConfig = $magentoCcConfig;
        $this->omiseCcConfig   = $omiseCcConfig;
        $this->customer        = $customer;
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
            'payment' => [
                'ccform' => [
                    'months' => [OmiseCcConfig::CODE => $this->magentoCcConfig->getCcMonths()],
                    'years'  => [OmiseCcConfig::CODE => $this->magentoCcConfig->getCcYears()],
                ],
                OmiseCcConfig::CODE => [
                    'publicKey'          => $this->omiseCcConfig->getPublicKey(),
                    'offsitePayment'     => $this->omiseCcConfig->is3DSecureEnabled(),
                    'isCustomerLoggedIn' => $this->customer->isLoggedIn(),
                    'cards'              => $this->getCards(),
                ],
                'capabilities' => $this->getCapabilities(),
            ],
        ];
    }

    /**
     * @return  array
     */
    public function getCards()
    {
        if (! $this->customer->getMagentoCustomerId() || ! $this->customer->getId()) {
            return [];
        }

        $cards = $this->customer->cards(['order' => 'reverse_chronological']);

        if (! $cards) {
            return [];
        }

        $data = [];

        foreach ($cards['data'] as $card) {
            $label = $card['brand'] . ' **** ' . $card['last_digits'];
            $data[] = [
                'value' => $card['id'],
                'label' => $label
            ];
        }

        return $data;
    }
}
