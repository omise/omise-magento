<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Ocbcpao extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_ocbcpao';

    /**
     * Backends identifier
     * @var string
     */ 
    const ID = "mobile_banking_ocbc_pao";
}
