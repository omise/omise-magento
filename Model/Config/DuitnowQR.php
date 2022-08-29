<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class DuitnowQR extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_duitnowqr';

    /**
     * Backends identifier
     * @var string
     */
    const ID = 'duitnow_qr';
}
