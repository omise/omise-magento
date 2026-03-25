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
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/omise-upa.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('***APMPayment***');
        
        $transferObjectBody = $transferObject->getBody();
        $methodCode = $transferObjectBody['payment_methods'][0];
        $logger->info(print_r($methodCode,true));
        $isUpaAllow = $this->omiseHelper->isAllowUpa($methodCode);

        if($isUpaAllow){
            return ["session" => $this->apiCharge->createSession($transferObjectBody)];
        }else{
            return [self::CHARGE => $this->apiCharge->create($transferObjectBody)];
        }
    }
}
