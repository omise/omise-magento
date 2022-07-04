<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Mobilebanking extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_mobilebanking';

    const SCB_NAME = "mobile_banking_scb";
    const KBANK_NAME ="mobile_banking_kbank";
    const BBL_NAME ="mobile_banking_bbl";
    const BAY_NAME= "mobile_banking_bay";
}
