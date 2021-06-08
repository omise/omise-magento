<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class TruemoneyDataAssignObserver extends OffsiteDataAssignObserver
{
    /**
     * @var string
     */
    const PHONE_NUMBER   = 'truemoney_phone_number';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::PHONE_NUMBER
    ];
}
