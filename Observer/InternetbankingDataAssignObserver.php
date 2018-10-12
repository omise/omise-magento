<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class InternetbankingDataAssignObserver extends AbstractDataAssignObserver
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

    private $log;
    public function __construct(
        \PSR\Log\LoggerInterface $log
    ) {
        $this->log = $log;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->log->debug('observer', ['observer'=>$observer]);
        $dataObject = $this->readDataArgument($observer);

        $additionalData = $dataObject->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (! is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}
