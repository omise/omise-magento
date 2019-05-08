<?php

namespace Omise\Payment\Gateway\Http\Client;

class CCPayment extends AbstractPayment
{
    /**
     * @param  \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     *
     * @return \Omise\Payment\Model\Api\Charge|\Omise\Payment\Model\Api\Error
     */
    public function placeRequest(\Magento\Payment\Gateway\Http\TransferInterface $transferObject)
    {
        $transferObjectBody = $transferObject->getBody();

        // if charge_id already exists than action is 'manual capture'
        if (isset($transferObjectBody[self::CHARGE_ID])) {
            $charge = $this->apiCharge->find($transferObjectBody[self::CHARGE_ID]);
            return [self::CHARGE => $charge->capture()];
        }

        return [self::CHARGE => $this->apiCharge->create($transferObjectBody)];
    }
}
