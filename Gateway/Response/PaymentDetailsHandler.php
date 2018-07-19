<?php
namespace Omise\Payment\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = SubjectReader::readPayment($handlingSubject);
        $payment = $payment->getPayment();

        $payment->setAdditionalInformation('charge_id', $response['charge']->id);
        $payment->setAdditionalInformation('charge_authorize_uri', $response['charge']->authorize_uri);
        $payment->setAdditionalInformation('payment_type', $response['charge']->source['type']);
        
        if ($response['charge']->source['type'] === 'bill_payment_tesco_lotus') {
            $payment->setAdditionalInformation('barcode', $response['charge']->source['references']['barcode']);
        }
    }
}
