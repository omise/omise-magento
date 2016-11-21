<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Observer\OmiseDataAssignObserver;

class PaymentDataBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    const OMISE_TOKEN = 'omise_card_token';

    /**
     * @var string
     */
    const AMOUNT = 'amount';

    /**
     * @var string
     */
    const CURRENCY = 'currency';

    /**
     * @param \Omise\Payment\Helper\OmiseHelper $omiseHelper
     */
    public function __construct(OmiseHelper $omiseHelper)
    {
        $this->omiseHelper = $omiseHelper;
    }

    /**
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $method  = $payment->getPayment();
        $order   = $payment->getOrder();

        return [
            self::AMOUNT      => $this->omiseHelper->omiseAmountFormat($order->getCurrencyCode(), $order->getGrandTotalAmount()),
            self::CURRENCY    => $order->getCurrencyCode(),
            self::OMISE_TOKEN => $method->getAdditionalInformation(OmiseDataAssignObserver::OMISE_CARD_TOKEN)
        ];
    }
}
