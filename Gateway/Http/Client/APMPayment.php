<?php

namespace Omise\Payment\Gateway\Http\Client;

class APMPayment extends AbstractPayment
{
    /**
     * @param  \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     *
     * @return \Omise\Payment\Model\Api\Charge|\Omise\Payment\Model\Api\Error
     */
    public function placeRequest(\Magento\Payment\Gateway\Http\TransferInterface $transferObject)
    {
        $transferObjectBody = $transferObject->getBody();

        return [self::CHARGE => $this->apiCharge->create($transferObjectBody)];
    }
}
