<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Paynow extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offline_paynow';
    
    /**
     * Backends identifier
     * @var string
     */
    const ID ='paynow';
}
