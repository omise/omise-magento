<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class InstallmentDataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * @var string
     */
    const OFFSITE = 'offsite';
    const TERMS   = 'terms';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::OFFSITE,
        self::TERMS
    ];
    private $log;
    public function __construct(\PSR\Log\LoggerInterface $log){
        $this->log = $log;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        $dataObject = $this->readDataArgument($observer);

        $additionalData = $dataObject->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        $this->log->debug('from observer installment', ['msg'=>$additionalData]);

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
