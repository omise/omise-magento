<?php

namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Fpx extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_fpx';

     /**
     * @var string
     */
    const TYPE = 'fpx';

    /**
     * Backends identifier
     * @var string
     */
    const ID = 'fpx';
}
