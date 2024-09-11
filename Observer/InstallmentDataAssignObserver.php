<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

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
     * @var array
     */
    protected $additionalInformationList = [
        self::OFFSITE,
        self::TERMS,
        self::CARD,
        self::SOURCE,
    ];
}
