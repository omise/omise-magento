<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Mobilebanking extends Config
{
    /**
     * @var string
     */
    const OCBCPAO_CODE = 'omise_offsite_mobilebanking_ocbc_pao';
    const BAY_CODE = 'omise_offsite_mobilebanking_bay';
}