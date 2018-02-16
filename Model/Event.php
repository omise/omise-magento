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

    /**
     * @param \Omise\Payment\Model\Omise     $omise
     * @param \Omise\Payment\Model\Order     $order
     * @param \Omise\Payment\Model\Api\Event $apiEvent
     */
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
     * @param  Object $payload
     *
     * @return mixed
     */
    public function handle($payload)
    {
        $event = $this->apiEvent->find($payload->id);

        if (! $event instanceof ApiEvent) {
            // TODO: Handle in case can't retrieve an event object from '$payload->id'.
            return;
        }

        if (! isset($this->events[$event->key])) {
            // TODO: Handle in case can't retrieve an event object from '$payload->id'.
            return;
        }

        return (new $this->events[$event->key])->handle($event, $this->order);
    }
}
