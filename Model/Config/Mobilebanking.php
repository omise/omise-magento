<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Mobilebanking extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_mobilebanking';

    /**
     * Backends identifier
     * @var string
     */
    const SCB_ID = "mobile_banking_scb";
    const KBANK_ID ="mobile_banking_kbank";
    const BBL_ID ="mobile_banking_bbl";
    const BAY_ID= "mobile_banking_bay";
}
