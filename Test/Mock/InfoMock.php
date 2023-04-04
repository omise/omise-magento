<?php

namespace Omise\Payment\Test\Mock;

use Magento\Payment\Model\InfoInterface;

class InfoMock implements InfoInterface
{
    public function encrypt($data)
    {
    }

    public function decrypt($data)
    {
    }

    public function setAdditionalInformation($key, $value = null)
    {
    }

    public function hasAdditionalInformation($key = null)
    {
    }

    public function unsAdditionalInformation($key = null)
    {
    }

    public function getAdditionalInformation($key = null)
    {
    }

    public function getMethodInstance()
    {
    }

    public function getMethod()
    {
    }

    public function getPayment()
    {
    }
}
