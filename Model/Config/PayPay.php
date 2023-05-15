<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class PayPay extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_paypay';

    /**
     * Backends identifier
     * @var string
     */
    const ID = 'paypay';
}
