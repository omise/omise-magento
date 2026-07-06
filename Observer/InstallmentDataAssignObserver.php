<?php
namespace Omise\Payment\Observer;

class InstallmentDataAssignObserver extends OffsiteDataAssignObserver
{
    /**
     * @var string
     */
    const OFFSITE = 'offsite';

    /**
     * @var string
     */
    const TERMS   = 'terms';
    const CARD    = 'card';
    const SOURCE  = 'source';

    /**
     * @var string
     */
    const WLB = 'isWlb';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::WLB,
        self::OFFSITE,
        self::TERMS,
        self::CARD,
        self::SOURCE,
    ];
}
