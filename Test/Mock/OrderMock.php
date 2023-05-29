<?php

namespace Omise\Payment\Test\Mock;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;

class OrderMock implements OrderAdapterInterface
{
    public function getCurrencyCode()
    {
    }

    public function getOrderIncrementId()
    {
    }

    public function getCustomerId()
    {
    }

    public function getBillingAddress()
    {
    }

    public function getShippingAddress()
    {
    }

    public function getStoreId()
    {
    }

    public function getId()
    {
    }

    public function getGrandTotalAmount()
    {
    }

    public function getItems()
    {
    }

    public function getRemoteIp()
    {
    }

    public function getSubTotal()
    {
    }
}
