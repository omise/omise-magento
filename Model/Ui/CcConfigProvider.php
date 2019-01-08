<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Model\CcConfig as MagentoCcConfig;
use Omise\Payment\Model\Config\Cc as OmiseCcConfig;
use Omise\Payment\Model\Customer;

class CcConfigProvider implements ConfigProviderInterface
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

    public function __construct(
        MagentoCcConfig $magentoCcConfig,
        OmiseCcConfig   $omiseCcConfig,
        Customer        $customer
    ) {
        $this->magentoCcConfig = $magentoCcConfig;
        $this->omiseCcConfig   = $omiseCcConfig;
        $this->customer        = $customer;
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
            ]
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

        foreach($cards['data'] as $card) {
            $label = $card['brand'] . ' **** ' . $card['last_digits'];
            $data[] = [
                'value' => $card['id'],
                'label' => $label
            ];
        }

        return $data;
    }
}
