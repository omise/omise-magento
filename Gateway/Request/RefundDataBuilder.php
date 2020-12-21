<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omise\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Sales\Model\Order\Payment;
use Omise\Payment\Helper\OmiseHelper;

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
     * Constructor
     *
     * @param SubjectReader $subjectReader
     * @param OmiseHelper $omiseHelper
     */
    public function __construct(
        SubjectReader $subjectReader,
        OmiseHelper $omiseHelper
    ) {
        $this->subjectReader = $subjectReader;
        $this->omiseHelper = $omiseHelper;
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
        $order = $paymentDO->getOrder();
        
        return [
            'transaction_id' => $payment->getParentTransactionId(),
            PaymentDataBuilder::AMOUNT => $this->omiseHelper->omiseAmountFormat(
                $order->getCurrencyCode(),
                $order->getGrandTotalAmount()
            )
        ];
    }
}
