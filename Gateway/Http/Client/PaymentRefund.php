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

        if (!isset($transferObjectBody[self::TRANSACTION_ID])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to process refund.'));
        }

        $storeId = $transferObjectBody['store_id'];
        $charge = $this->apiCharge->find($transferObjectBody[self::TRANSACTION_ID], $storeId);

        if (!$charge->refundable) {
            $sourceType = $charge->source['type'] ?? 'credit_card';
            $method = str_replace('_', ' ', $sourceType);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Payment with omise %1 cannot be refunded.', $method)
            );
        }

        unset($transferObjectBody[self::TRANSACTION_ID]);
        return [self::REFUND => $charge->refund($transferObjectBody, $storeId)];
    }
}
