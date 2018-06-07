<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Payment\Model\InfoInterface;

class CreditCardDataObserver extends AbstractDataAssignObserver
{
    /**
     * @var string
     */
    const TOKEN         = 'omise_card_token';
    const REMEMBER_CARD = 'omise_save_card';


    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::TOKEN,
        self::REMEMBER_CARD
    ];

    /**
     * Handle 'payment_method_assign_data_omise_cc' event.
     *
     * @param  \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @see    etc/events.xml
     * @see    Magento\Payment\Model\Method\AbstractMethod::assignData()
     */
    public function execute(Observer $observer)
    {
        $dataObject     = $this->readDataArgument($observer);
        $additionalData = $dataObject->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (! is_array($additionalData)) {
            return;
        }

        $this->setPaymentAdditionalInformation($this->readPaymentModelArgument($observer), $additionalData);
    }

    /**
     * @param  \Magento\Payment\Model\InfoInterface $paymentInfo
     * @param  array                                $additionalData
     *
     * @return void
     */
    protected function setPaymentAdditionalInformation(InfoInterface $paymentInfo, array $additionalData)
    {
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
