<?php

namespace Omise\Payment\Gateway\Http\Client;
use Omise\Payment\Model\Api\Charge as ApiCharge;
use Omise\Payment\Model\Omise;
use Omise\Payment\Helper\OmiseHelper;

class APMPayment extends AbstractPayment
{
    /**
     * @var OmiseHelper
     */
    private $omiseHelper;

    /**
     * @param ApiCharge $apiCharge,
     * @param Omise $omise
     * @param OmiseHelper $omiseHelper
     */
    public function __construct(
        ApiCharge $apiCharge,
        Omise $omise,
        OmiseHelper $omiseHelper
    ) {
        $this->omiseHelper = $omiseHelper;
        parent::__construct($apiCharge, $omise);
    }

    /**
     * @param  \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     *
     * @return \Omise\Payment\Model\Api\Charge|\Omise\Payment\Model\Api\Error
     */
    public function placeRequest(\Magento\Payment\Gateway\Http\TransferInterface $transferObject)
    {
        $transferObjectBody = $transferObject->getBody();

        if(array_key_exists('is_upa',$transferObjectBody)){
            return [self::SESSION => $this->apiCharge->createSession($transferObjectBody)];
        }else{
            return [self::CHARGE => $this->apiCharge->create($transferObjectBody)];
        }
    }
}
