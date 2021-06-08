<?php

namespace Omise\Payment\Observer;

class FpxDataAssignObserver extends OffsiteDataAssignObserver
{
    /**
     * @var string
     */
    const BANK   = 'bank';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::BANK
    ];
}
