<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class MaybankQR extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_maybankqr';

    /**
     * Backends identifier
     * @var string
     */
    const ID = 'maybank_qr';
}
