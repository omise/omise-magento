<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Observer\OmiseDataAssignObserver;

class PaymentDataBuilder implements BuilderInterface
{

    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject)->getPayment();

        return [
            'omise_card_token' => $payment->getAdditionalInformation(OmiseDataAssignObserver::OMISE_CARD_TOKEN)
        ];
    }
}
