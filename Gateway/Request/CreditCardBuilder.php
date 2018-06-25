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
    const CARD     = 'card';
    const CUSTOMER = 'customer';

    /**
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $method  = $payment->getPayment();
        $card    = $method->getAdditionalInformation(CreditCardDataObserver::CARD);

        if ($method->getAdditionalInformation(CreditCardDataObserver::CUSTOMER)) {
            if ($card) {
                return [
                    self::CARD     => $card,
                    self::CUSTOMER => $method->getAdditionalInformation(CreditCardDataObserver::CUSTOMER)
                ];
            }

            return [ self::CUSTOMER => $method->getAdditionalInformation(CreditCardDataObserver::CUSTOMER) ];
        }

        return [ self::CARD => $method->getAdditionalInformation(CreditCardDataObserver::TOKEN) ];
    }
}
