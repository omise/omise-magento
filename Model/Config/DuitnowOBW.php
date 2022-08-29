<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class DuitnowOBW extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_duitnowobw';

    /**
     * Backends identifier
     * @var string
     */
    const ID = 'duitnow_obw';
}
