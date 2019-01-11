<?php
namespace Omise\Payment\Model;

use Omise\Payment\Model\Api\Capabilities as OmiseCapabilitiesAPI;
use Omise\Payment\Model\Omise;

class Capabilities
{
    /**
     * @var \Omise\Payment\Model\Omise
     */
    protected $omise;

    /**
     * @var \Omise\Payment\Model\Api\Capabilities
     */
    protected $capabilitiesAPI;

    public function __construct(
        Omise $omise,
        OmiseCapabilitiesAPI $capabilitiesAPI
    )
    {
        $this->omise = $omise;
        $this->capabilitiesAPI = $capabilitiesAPI;

        $this->omise->defineUserAgent();
        $this->omise->defineApiVersion();
        $this->omise->defineApiKeys();
    }

    /**
     * @return array
     */
    public function retrieveInstallmentBackends()
    {
        return $this->capabilitiesAPI->getInstallmentBackends();
    }
}
