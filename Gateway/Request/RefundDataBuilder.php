<?php

namespace Omise\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Sales\Model\Order\Payment;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\OmiseMoney;

class RefundDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var OmiseHelper
     */
    protected $omiseHelper;

     /**
     * @var OmiseMoney
     */
    protected $money;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     * @param OmiseHelper $omiseHelper
     */
    public function __construct(
        SubjectReader $subjectReader,
        OmiseHelper $omiseHelper,
        OmiseMoney $money
    ) {
        $this->subjectReader = $subjectReader;
        $this->omiseHelper = $omiseHelper;
        $this->money = $money;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $order = $payment->getOrder();
        $currency = $order->getOrderCurrency()->getCode();
        $amountToRefund = $order->getTotalOnlineRefunded();

        return [
            'store_id' => $order->getStore()->getId(),
            'transaction_id' => $payment->getParentTransactionId(),
            PaymentDataBuilder::AMOUNT => $this->money->parse($amountToRefund, $currency)->toSubunit(),
        ];
    }
}
