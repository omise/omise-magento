<?php

namespace Omise\Payment\Model;

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
     * @param  Omise_Gateway_Model_Api_Event $event
     *
     * @return mixed
     */
    public function handle($event)
    {
        if (! isset($this->events[$event->key])) {
            return;
        }

        return (new $this->events[$event->key])->handle($event);
    }
}
