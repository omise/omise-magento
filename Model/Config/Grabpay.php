<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Grabpay extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_grabpay';
    
    /**
     * Backends identifier
     * @var string
     */
    const ID = 'grabpay';
}
