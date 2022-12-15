<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;
use Omise\Payment\Model\Config\ApmSourceInterface;

class Alipay extends Config implements ApmSourceInterface
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

    public static function getSourceData()
    {
        return [ 'type' => self::ID ];
    }
}
