<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Truemoney extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_truemoney';

    /**
     * Backends identifier
     * @var string
     */
    const ID = 'truemoney';
}
