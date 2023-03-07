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
    const ID = 'credit_card';


    public function getCardThemeConfig()
    {
        return $this->getValue('card_form_theme_config', self::CODE);
    }

    public function getCardTheme()
    {
        return $this->getValue('card_form_theme', self::CODE);
    }
}
