<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Rabbitlinepay extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_rabbitlinepay';

    /**
     * Backends identifier
     * @var string
     */
    const ID = 'rabbit_linepay';
}
