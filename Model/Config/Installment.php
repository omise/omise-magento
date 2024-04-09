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
     * Backends identifier
     *
     * @var string
     */
    const BAY_ID = "installment_bay";

    const BBL_ID = "installment_bbl";

    const FIRST_CHOICE_ID = "installment_first_choice";

    const KBANK_ID = "installment_kbank";

    const KTC_ID = "installment_ktc";

    const SCB_ID = "installment_scb";

    const TTB_ID = "installment_ttb";

    const UOB_ID = "installment_uob";

    const MBB_ID = "installment_mbb";
}
