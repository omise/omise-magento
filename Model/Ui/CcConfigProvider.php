<?php

namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Model\CcConfig as MagentoCcConfig;
use Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme;
use Omise\Payment\Model\Config\Cc as OmiseCcConfig;
use Omise\Payment\Model\Customer;
use Omise\Payment\Model\Capability;
use Omise\Payment\Helper\OmiseHelper;

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

    /** @var Capability */
    private $capability;

    /** @var OmiseHelper */
    private $omiseHelper;

    public function __construct(
        MagentoCcConfig $magentoCcConfig,
        OmiseCcConfig   $omiseCcConfig,
        Customer        $customer,
        Capability $capability,
        OmiseHelper $omiseHelper
    ) {
        $this->magentoCcConfig = $magentoCcConfig;
        $this->omiseCcConfig   = $omiseCcConfig;
        $this->customer        = $customer;
        $this->capability = $capability;
        $this->omiseHelper = $omiseHelper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $theme = new Theme();
        $customDesign = $this->omiseCcConfig->getCardThemeConfig();
        $selectedTheme = $this->omiseCcConfig->getCardTheme();
        $configuredCardIcons = $this->omiseCcConfig->getSupportedCardIcons();
        $apiCardIcons = $this->omiseHelper->normalizeCardBrands($this->capability->getCardBrands());
        $supportedCardIcons = array_values(array_intersect($configuredCardIcons, $apiCardIcons));
        return [
            'payment' => [
                'ccform' => [
                    'months' => [OmiseCcConfig::CODE => $this->magentoCcConfig->getCcMonths()],
                    'years'  => [OmiseCcConfig::CODE => $this->magentoCcConfig->getCcYears()],
                ],
                OmiseCcConfig::CODE => [
                    'publicKey'                     => $this->omiseCcConfig->getPublicKey(),
                    'isCustomerLoggedIn'            => $this->customer->isLoggedIn(),
                    'cards'                         => $this->getCards(),
                    'locale'                        => $this->omiseCcConfig->getStoreLocale(),
                    'formDesign'                    => $theme->getFormDesign($selectedTheme, $customDesign),
                    'theme'                         => $selectedTheme,
                    'supported_card_icons'     => $supportedCardIcons
                ],
            ]
        ];
    }

    /**
     * @return  array
     */
    public function getCards()
    {
        if (!$this->customer->getMagentoCustomerId() || !$this->customer->getId()) {
            return [];
        }

        $cards = $this->customer->cards(['order' => 'reverse_chronological']);

        if (!$cards) {
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
