<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Installment extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_installment';

   
      const BAY_NAME ="installment_bay";
      const BBL_NAME ="installment_bbl";
      const CITI_NAME ="installment_citi";
      const FIRST_CHOICE_NAME ="installment_first_choice";
      const KBANK_NAME ="installment_kbank";
      const KTC_NAME ="installment_ktc";
      const SCB_NAME ="installment_scb";
      const TTB_NAME ="installment_ttb";
      const UOB_NAME ="installment_uob";
}
