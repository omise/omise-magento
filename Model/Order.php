<?php

namespace Omise\Payment\Model;

use Magento\Sales\Model\Order as MagentoOrder;

class Order
{
    /** 
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function __construct(MagentoOrder $order)
    {
        $this->order = $order;
    }

    /**
     * @param  string $id
     *
     * @return \Magento\Sales\Model\Order
     */
    public function loadByIncrementId($id)
    {
        return $this->order->loadByIncrementId($id);
    }
}
