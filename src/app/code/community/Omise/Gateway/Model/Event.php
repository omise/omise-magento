<?php
class Omise_Gateway_Model_Event
{
    /**
     * @var array  of event classes that we can handle.
     */
    protected $events = array();

    public function __construct() {
        $events = array(
            'charge_complete'
        );

        foreach ($events as $event) {
            $clazz = Mage::getModel('omise_gateway/event_' . $event);
            $this->events[$clazz->event] = $clazz;
        }
    }

    /**
     * @param  Omise_Gateway_Model_Api_Event $event
     *
     * @return mixed
     */
    public function handle($event) {
        if (! isset($this->events[$event->key])) {
            return;
        }

        return $this->events[$event->key]->handle($event);
    }
}
