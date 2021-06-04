<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class ConveniencestoreDataAssignObserver extends OffsiteDataAssignObserver
{
    /**
     * @var string
     */
    const PHONE_NUMBER  = 'conv_store_phone_number';
    const EMAIL         = 'conv_store_email';
    const CUSTOMER_NAME = 'conv_store_customer_name';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::PHONE_NUMBER,
        self::EMAIL,
        self::CUSTOMER_NAME
    ];
}
