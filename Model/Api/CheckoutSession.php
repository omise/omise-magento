<?php

namespace Omise\Payment\Model\Api;

use Exception;
use OmiseApiResource;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\RequestHelper;
use Magento\Framework\Exception\LocalizedException;

class CheckoutSession extends BaseObject
{
    private $config;

    /**
     * @var RequestHelper
     */
    private $requestHelper;

    /**
     * @var OmiseHelper
     */
    private $omiseHelper;

    /**
     * Injecting dependencies
     *
     * @param Config $config
     * @param RequestHelper $apmSession
     * @param OmiseHelper $omiseHelper
     */
    public function __construct(
        Config $config,
        RequestHelper $requestHelper,
        OmiseHelper $omiseHelper
    ) {
        $this->requestHelper = $requestHelper;
        $this->config = $config;
        $this->omiseHelper = $omiseHelper;
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public function createSession($params)
    {
        try {
            $endpoint = $this->omiseHelper->checkoutSessionEndPoint();
            $session = $this->requestHelper->request(
                $endpoint."api/sessions",
                OmiseApiResource::REQUEST_POST,
                $this->config->getSecretKey(),
                $params,
                true
            );
            $this->refresh($session);
        } catch (Exception $e) {
            throw new LocalizedException(__('Failed to create session : ' . $e->getMessage()));
        }
        return $this;
    }

    /**
     * @param string $sessionId
     *
     * @return Omise\Payment\Model\Api\Error|self
     */
    public function getSessionInfo($sessionId)
    {
        try {
            $endpoint = $this->omiseHelper->checkoutSessionEndPoint();
            $session = $this->requestHelper->request(
                $endpoint."api/sessions/".$sessionId,
                OmiseApiResource::REQUEST_GET,
                $this->config->getSecretKey()
            );
            $this->refresh($session);
        } catch (Exception $e) {
            throw new LocalizedException(__('Failed to get session info : ' . $e->getMessage()));
        }
        return $this;
    }
}
