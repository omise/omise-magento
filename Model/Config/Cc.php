<?php

namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Cc extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_cc';

    /**
     * Backends identifier
     * @var string
     */
    const ID = 'card';

    public function getCardThemeConfig()
    {
        return $this->getValue('card_form_theme_config', self::CODE);
    }

    public function getCardTheme()
    {
        return $this->getValue('card_form_theme', self::CODE);
    }

    /**
     * Get the card brands icons enabled in the payment configuration.
     *
     * @return array
     */
    public function getSupportedCardIcons()
    {
        $supportedCardIcons = $this->getValue('supported_card_icons', self::CODE);
        return $supportedCardIcons ? explode(',', $supportedCardIcons) : [];
    }
}
