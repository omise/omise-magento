<?php
namespace Omise\Payment\Observer;

class AtomeDataAssignObserver extends OffsiteDataAssignObserver
{
    /**
     * @var string
     */
    const PHONE_NUMBER   = 'atome_phone_number';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::PHONE_NUMBER
    ];
}
