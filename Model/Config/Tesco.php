<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;
use Omise\Payment\Model\Config\ApmSourceInterface;

class Tesco extends Config implements ApmSourceInterface
{
    /**
     * @var string
     */
    const CODE = 'omise_offline_tesco';

    /**
     * Backends identifier
     * @var string
     */
    const ID = 'bill_payment_tesco_lotus';

    public static function getSourceData()
    {
        return [ 'type' => self::ID ];
    }
}
