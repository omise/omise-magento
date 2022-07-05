<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Internetbanking extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_internetbanking';

    /**
     * Backends identifier
     * @var string
     */
    const KTB_ID ="internet_banking_ktb"; 
    const BAY_ID ="internet_banking_bay"; 
    const BBL_ID ="internet_banking_bbl"; 
    const SCB_ID ="internet_banking_scb"; 
}
