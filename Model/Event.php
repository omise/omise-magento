<?php

namespace Omise\Payment\Model;

use Omise\Payment\Model\Omise;
use Omise\Payment\Model\Order;
use Omise\Payment\Model\Api\Event as ApiEvent;
use Omise\Payment\Model\Event\Charge\Complete as EventChargeComplete;

class Event
{
    /**
     * @var array  of event classes that we can handle.
     */
    protected $events = [
        EventChargeComplete::CODE => EventChargeComplete::class
    ];

    /**
     * @var \Omise\Payment\Model\Api\Event
     */
    protected $apiEvent;

    public function __construct(
        Omise    $omise,
        Order    $order,
        ApiEvent $apiEvent
    ) {
        $this->order    = $order;
        $this->apiEvent = $apiEvent;

        $omise->defineUserAgent();
        $omise->defineApiVersion();
        $omise->defineApiKeys();
    }

    /**
     * @param  string $id
     *
     * @return \Omise\Payment\Model\Api\Event|\Omise\Payment\Model\Api\Error
     */
    public function find($id)
    {
        return $this->apiEvent->find($id);
    }

    /**
     * @param  \Omise\Payment\Model\Api\Event $event
     *
     * @return mixed
     */
    public function handle(ApiEvent $event)
    {
        if (! isset($this->events[$event->key])) {
            return;
        }

        return (new $this->events[$event->key])->handle($event, $this->order);
    }
}
