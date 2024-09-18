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

    /**
     * WLB Installments
     */
    const WLB_BAY_ID = "installment_wlb_bay";

    const WLB_BBL_ID = "installment_wlb_bbl";

    const WLB_FIRST_CHOICE_ID = "installment_wlb_first_choice";

    const WLB_KBANK_ID = "installment_wlb_kbank";

    const WLB_KTC_ID = "installment_wlb_ktc";

    const WLB_SCB_ID = "installment_wlb_scb";

    const WLB_TTB_ID = "installment_wlb_ttb";

    const WLB_UOB_ID = "installment_wlb_uob";
}
