<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Boost extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_boost';

    /**
     * Backends identifier
     * @var string
     */
    const ID = 'boost';
}
