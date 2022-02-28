<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class MobilebankingDataAssignObserver extends OffsiteDataAssignObserver
{
    /**
     * @var string
     */
    const OFFSITE = 'offsite';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::OFFSITE
    ];
}
