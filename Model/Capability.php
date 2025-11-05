<?php

namespace Omise\Payment\Model;

use Omise\Payment\Model\Api\Capability as OmiseCapabilityAPI;
use Omise\Payment\Model\Omise;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\OmiseMoney;

class Capability
{
    /**
     * @var \Omise\Payment\Model\Omise
     */
    protected $omise;

    /**
     * @var \Omise\Payment\Model\Api\Capability
     */
    protected $capabilityAPI;

    /**
     * @var Omise\Payment\Helper\OmiseHelper
     */
    protected $helper;

    /**
     * @var Omise\Payment\Helper\OmiseMoney;
     */
    protected $money;

    public function __construct(
        Omise $omise,
        OmiseCapabilityAPI $capabilityAPI,
        OmiseHelper $helper,
        OmiseMoney $money
    ) {
        $this->omise = $omise;
        $this->capabilityAPI = $capabilityAPI;
        $this->helper = $helper;
        $this->money = $money;

        $this->omise->defineUserAgent();
        $this->omise->defineApiVersion();
        $this->omise->defineApiKeys();
    }

    /**
     * @return array
     */
    public function retrieveInstallmentBackends()
    {
        return $this->capabilityAPI->getInstallmentBackends();
    }

    /**
     * @return bool
     */
    public function isZeroInterest()
    {
        return $this->capabilityAPI->isZeroInterest();
    }

    /**
     * @param string $type
     * @return array|null
     */
    public function getBackendsByType(string $type)
    {
        return $this->capabilityAPI->getBackendsByType($type);
    }

    /**
     *
     * @return array|null
     */
    public function retrieveMobileBankingBackends()
    {
        $backends = $this->capabilityAPI->getPaymentMethods();
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
    public function getPaymentMethods()
    {
        return $this->capabilityAPI->getPaymentMethods();
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
        $backends = $this->capabilityAPI->getPaymentMethods();
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
        return $this->capabilityAPI->getTokenizationMethods();
    }

    /**
     * @return integer
     */
    public function getInstallmentMinLimit($currency)
    {
        $amount = $this->capabilityAPI->getInstallmentMinLimit();
        return $this->money->setAmountAndCurrency(
            $amount,
            $currency
        )->toUnit();
    }

    /**
     *
     * @return object
     */
    public function getTokenizationMethodsWithOmiseCode()
    {
        $methods = $this->capabilityAPI->getTokenizationMethods();
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
