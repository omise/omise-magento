<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Observer\OffsiteInternetbankingDataAssignObserver;

class PaymentOffsiteBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    const OFFSITE = 'offsite';

    /**
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $method  = $payment->getPayment();

        return [
            self::OFFSITE => $method->getAdditionalInformation(OffsiteInternetbankingDataAssignObserver::OFFSITE)
        ];
    }
}
