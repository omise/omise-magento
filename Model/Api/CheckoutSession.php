<?php

namespace Omise\Payment\Model\Api;

use Exception;
use OmiseApiResource;
use Omise\Payment\Model\Config\Config;
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
     * Injecting dependencies
     *
     * @param Config $config
     * @param RequestHelper $requestHelper
     */
    public function __construct(
        Config $config,
        RequestHelper $requestHelper
    ) {
        $this->requestHelper = $requestHelper;
        $this->config = $config;
    }

    /**
     * @param array $params
     *
     * @return self
     * @throws LocalizedException
     */
    public function createSession($params)
    {
        try {
            $endpoint = $this->config->checkoutSessionEndpoint();
            $session = $this->requestHelper->sendUpaSessionRequest(
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
     * @return self
     * @throws LocalizedException
     */
    public function getSessionInfo($sessionId)
    {
        try {
            $endpoint = $this->config->checkoutSessionEndpoint();
            $session = $this->requestHelper->sendUpaSessionRequest(
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
