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
     * Backends identifier
     * @var string
     */
    const ID ='credit_card';
}
