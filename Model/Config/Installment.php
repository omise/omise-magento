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
    const KRUNGTHAI   = 'omise_offsite_installment_krungthai';
    const FIRSTCHOICE = 'omise_offsite_installment_fc';
    const BBL         = 'omise_offsite_installment_bbl';
    const KRUNGSRI    = 'omise_offsite_installment_krungsri';
}
