<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Tesco extends Config
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
}
