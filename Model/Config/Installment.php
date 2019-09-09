<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Installment extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_installment';

    const KBANK       = 'omise_offsite_installment_kbank';
    const KRUNGTHAI   = 'omise_offsite_installment_ktc';
    const FIRSTCHOICE = 'omise_offsite_installment_first_choice';
    const BBL         = 'omise_offsite_installment_bbl';
    const KRUNGSRI    = 'omise_offsite_installment_bay';
}
