<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Observer\CreditCardDataObserver;

class CreditCardBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    const CARD = 'card';

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
            self::CARD => $method->getAdditionalInformation(CreditCardDataObserver::TOKEN)
        ];
    }
}
