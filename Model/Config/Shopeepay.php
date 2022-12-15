<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Shopeepay extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_shopeepay';

    /**
     * Backends identifier
     * @var string
     */
    const ID = 'shopeepay';

    const JUMPAPP_ID = 'shopeepay_jumpapp';
}
