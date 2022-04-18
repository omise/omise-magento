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
    ) {
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

    /**
     * @return bool
     */
    public function isZeroInterest()
    {
        return $this->capabilitiesAPI->isZeroInterest();
    }

    /**
     * @param string $type
     * @return array|null
     */
    public function getBackendsByType(string $type)
    {
        return $this->capabilitiesAPI->getBackendsByType($type);
    }

    /**
     *
     * @return array|null
     */
    public function retrieveMobileBankingBackends() {
        $backends = $this->capabilitiesAPI->getBackends();
        return array_filter($backends, function ($obj) {
            if (isset($obj->_id)) {
                if (preg_match('/mobile_banking_\S+/m', $obj->_id)) {
                    return true;
                }
            }
        });
    }
    
    /**
     * 
     * @return array|null
     */
    public function getBackends()
    {
        return $this->capabilitiesAPI->getBackends();
    }
}
