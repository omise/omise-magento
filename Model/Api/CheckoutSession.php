<?php

namespace Omise\Payment\Model\Api;

use Exception;
use OmiseCharge;
<<<<<<< .merge_file_TG97YV
=======
use OmiseApiResource;
>>>>>>> .merge_file_tdBYFV
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Helper\OmiseHelper;
use \Omise\Payment\Gateway\Http\Client\APMSession;
use Magento\Framework\Exception\LocalizedException;

class CheckoutSession extends BaseObject
{
    private $config;

    /**
     * @var APMSession
     */
    private $aPMSession;

    /**
     * @var OmiseHelper
     */
    private $omiseHelper;

    /**
     * Injecting dependencies
     *
     * @param Config $config
     * @param APMSession $aPMSession
     * @param OmiseHelper $omiseHelper
     */
    public function __construct(
        Config $config,
        APMSession $aPMSession,
        OmiseHelper $omiseHelper
    ){
        $this->aPMSession = $aPMSession;
        $this->config = $config;
        $this->omiseHelper = $omiseHelper;
    }

    /**
     * @param array $params
     * 
     * @return Omise\Payment\Model\Api\Error|self
     */
    public function createSession($params){        
        try {
<<<<<<< .merge_file_TG97YV
            $endPoint = $this->omiseHelper->checkoutSessionEndPoint();
            $session = $this->aPMSession->createSession($endPoint,$this->config->getSecretKey(),$params);
=======
            $session = $this->aPMSession->createSession($this->omiseHelper->checkoutSessionEndPoint(),OmiseApiResource::REQUEST_POST,$this->config->getSecretKey(),$params,true);
            $this->refresh($session);
        } catch (Exception $e) {
            throw new LocalizedException(__('Failed to charge : ' . $e->getMessage()));
        }
        return $this;
    }

    /**
     * @var string
     */
    public function getSessionInfo($sessionId){
        try {
            $url = $this->omiseHelper->checkoutSessionEndPoint().'/'.$sessionId;
            $session = $this->aPMSession->createSession($url,OmiseApiResource::REQUEST_GET,$this->config->getSecretKey(),$sessionId);
>>>>>>> .merge_file_tdBYFV
            $this->refresh($session);
        } catch (Exception $e) {
            throw new LocalizedException(__('Failed to charge : ' . $e->getMessage()));
        }
        return $this;
    }
}
