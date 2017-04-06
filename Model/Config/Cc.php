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
     * Check if Omise's sandbox mode enable or not
     *
     * @return bool
     */
    public function is3DSecureEnabled()
    {
        if ($this->getValue('3ds', self::CODE)) {
            return true;
        }

        return false;
    }
}
