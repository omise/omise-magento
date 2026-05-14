<?php

namespace Omise\Payment\Model\Api;

use Exception;
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
    private $apmSession;

    /**
     * @var OmiseHelper
     */
    private $omiseHelper;

    /**
     * Injecting dependencies
     *
     * @param Config $config
     * @param APMSession $apmSession
     * @param OmiseHelper $omiseHelper
     */
    public function __construct(
        Config $config,
        APMSession $apmSession,
        OmiseHelper $omiseHelper
    ) {
        $this->apmSession = $apmSession;
        $this->config = $config;
        $this->omiseHelper = $omiseHelper;
    }

    /**
     * @param array $params
     *
     * @return Omise\Payment\Model\Api\Error|self
     */
    public function createSession($params)
    {
        try {
            $endpoint = $this->omiseHelper->checkoutSessionEndpoint();
            $session = $this->apmSession->createSession(
                $endpoint."api/sessions",
                $this->config->getSecretKey(),
                $params
            );
            $this->refresh($session);
        } catch (Exception $e) {
            throw new LocalizedException(__('Failed to charge : ' . $e->getMessage()));
        }
        return $this;
    }
}
