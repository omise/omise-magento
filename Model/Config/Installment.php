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
    const KBANK = CODE + '_kbank';

    /**
     * @var string
     */
    const KRUNGTHAI = CODE + '_ktc';

    /**
     * @var string
     */
    const FIRSTCHOICE = CODE + '_first_choice';

    /**
     * @var string
     */
    const BBL = CODE + '_bbl';

    /**
     * @var string
     */
    const KRUNGSRI = CODE + '_bay';
}
