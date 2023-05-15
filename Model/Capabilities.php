<?php
namespace Omise\Payment\Model;

use Omise\Payment\Model\Api\Capabilities as OmiseCapabilitiesAPI;
use Omise\Payment\Model\Omise;
use Omise\Payment\Helper\OmiseHelper;

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

    /**
     * @var Omise\Payment\Helper\OmiseHelper
     */
    protected $helper;

    public function __construct(
        Omise $omise,
        OmiseCapabilitiesAPI $capabilitiesAPI,
        OmiseHelper $helper
    ) {
        $this->omise = $omise;
        $this->capabilitiesAPI = $capabilitiesAPI;
        $this->helper = $helper;

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
    public function retrieveMobileBankingBackends()
    {
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

    public function isBackendEnabled($type)
    {
        return $this->getBackendsByType($type) ? true : false;
    }

    /**
     *
     * @return object
     */
    public function getBackendsWithOmiseCode()
    {
        $backends = $this->capabilitiesAPI->getBackends();
        $list = [];
        if ($backends) {
            foreach ($backends as $backend) {
                $code = $this->helper->getOmiseCodeByOmiseId($backend->_id);
                if (isset($code)) {
                    $list[$code][] = $backend;
                }
            }
        }
        return $list;
    }

    /**
     *
     * @return array|null
     */
    public function getCardBrands()
    {
        $card = $this->getBackendsByType("card");
        return $card ? current($card)->brands : [];
    }

    /**
     *
     * @return array|null
     */
    public function getTokenizationMethods()
    {
        return $this->capabilitiesAPI->getTokenizationMethods();
    }

    /**
     *
     * @return object
     */
    public function getTokenizationMethodsWithOmiseCode()
    {
        $methods = $this->capabilitiesAPI->getTokenizationMethods();
        $list = [];
        if ($methods) {
            foreach ($methods as $method) {
                $code = $this->helper->getOmiseCodeByOmiseId($method);
                if (isset($code)) {
                    $list[$code][] = $methods;
                }
            }
        }
        return $list;
    }
}
