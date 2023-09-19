<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class OcbcDigital extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_ocbc_digital';

    /**
     * Backends identifier
     * @var string
     */
    const ID = "mobile_banking_ocbc";
}
