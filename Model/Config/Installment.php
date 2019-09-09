<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Installment extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_installment';

    /**
     * @var string
     */
    const KBANK = 'omise_offsite_installment_kbank';

    /**
     * @var string
     */
    const KRUNGTHAI = 'omise_offsite_installment_ktc';

    /**
     * @var string
     */
    const FIRSTCHOICE = 'omise_offsite_installment_first_choice';

    /**
     * @var string
     */
    const BBL = 'omise_offsite_installment_bbl';

    /**
     * @var string
     */
    const KRUNGSRI = 'omise_offsite_installment_bay';
}
