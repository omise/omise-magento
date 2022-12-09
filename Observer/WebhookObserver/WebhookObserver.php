<?php

namespace Omise\Payment\Observer\WebhookObserver;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omise\Payment\Model\Omise;
use Omise\Payment\Model\Order;
use Omise\Payment\Model\Api\Event as ApiEvent;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Model\Api\Charge as ApiCharge;

abstract class WebhookObserver implements ObserverInterface
{
    /**
     * @var Magento\Sales\Model\Order\Payment\Interceptor
     */
    protected $payment;

    /**
     * @var Magento\Sales\Model\Order\Invoice\Interceptor
     */
    protected $invoice;

    /**
     * @var Magento\Sales\Model\Order\Interceptor
     */
    protected $order;

    /**
     * @var Omise\Payment\Model\Api\Charge
     */
    protected $charge;

    /**
     * Order data fetched by order ID
     *
     * @var Magento\Sales\Model\Order\Interceptor
     */
    protected $orderData;

    /**
     * @param \use Omise\Payment\Model\Api\Event $apiEvent
     * @param \Omise\Payment\Model\Order $order
     * @param \Omise\Payment\Model\Config\Config $config
     */
    public function __construct(ApiEvent $apiEvent, Order $order, Config $config)
    {
        $this->order = $order;
        $this->apiEvent = $apiEvent;
        $this->config = $config;
    }

    public function setUpExecute(Observer $observer)
    {
        $this->charge = $observer->getData('data');

        if (! $this->charge instanceof ApiCharge || $this->charge->getMetadata('order_id') == null) {
            // TODO: Handle in case of improper response structure.
            return false;
        }

        $this->orderData = $this->order->loadByIncrementId($this->charge->getMetadata('order_id'));

        if (! $this->orderData->getId()) {
            // TODO: Handle in case of improper response structure.
            return false;
        }

        if (! $this->payment = $this->orderData->getPayment()) {
            // TODO: Handle in case of improper response structure.
            return false;
        }

        return true;
    }

    abstract public function execute(Observer $observer);
}
