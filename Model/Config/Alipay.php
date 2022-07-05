<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Alipay extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_alipay';
   
    /**
     * Backends identifier
     * @var string
     */
    const ID = 'alipay';
}
