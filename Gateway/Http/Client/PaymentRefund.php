<?php

namespace Omise\Payment\Gateway\Http\Client;

class PaymentRefund extends AbstractPayment
{
    const TRANSACTION_ID = 'transaction_id';
    const AMOUNT = 'amount';
    /**
     * Process http request
     * @param \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     * @return OmiseRefund|NULL
     */
    public function placeRequest(\Magento\Payment\Gateway\Http\TransferInterface $transferObject)
    {
        $transferObjectBody = $transferObject->getBody();
        if (isset($transferObjectBody[self::TRANSACTION_ID])) {
            $charge = $this->apiCharge->find($transferObjectBody[self::TRANSACTION_ID]);
            unset($transferObjectBody[self::TRANSACTION_ID]);
            return [self::REFUND => $charge->refund($transferObjectBody)];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to process refund.'));
        }
    }
}
