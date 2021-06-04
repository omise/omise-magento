<?php

namespace Omise\Payment\Observer;

class FPXDataAssignObserver extends OffsiteDataAssignObserver
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