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
    const CARD      = 'card';
    const CUSTOMER  = 'customer';
    const CHARGE_ID = 'charge_id';

    /**
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $method = $payment->getPayment();

        $paymentData = null;

        if ($method->getAdditionalInformation(CreditCardDataObserver::CUSTOMER)) {
            $paymentData = [
                self::CUSTOMER => $method->getAdditionalInformation(CreditCardDataObserver::CUSTOMER),
                self::CARD => $method->getAdditionalInformation(CreditCardDataObserver::CARD),
            ];
        } else {
            $paymentData = [
                self::CARD => $method->getAdditionalInformation(CreditCardDataObserver::TOKEN)
            ];
        }

        //add information about charge_id, if charge id exists than it is 'manual capture' request.
        $paymentData[self::CHARGE_ID] = $method->getAdditionalInformation(CreditCardDataObserver::CHARGE_ID);

        return $paymentData;
    }
}
