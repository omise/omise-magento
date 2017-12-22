<?php

namespace Omise\Payment\Controller\Callback;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Omise\Payment\Model\Omise;
use Omise\Payment\Model\Api\Event;
use Omise\Payment\Model\Api\Error;

class Webhook extends Action
{
    /**
     * @var Omise\Payment\Model\Omise
     */
    protected $omise;

    /**
     * @var \Omise\Payment\Model\Api\Event
     */
    protected $event;

    public function __construct(
        Context $context,
        Omise   $omise,
        Event   $event
    ) {
        $this->omise = $omise;
        $this->event = $event;

        parent::__construct($context);

        $this->omise->defineUserAgent();
        $this->omise->defineApiVersion();
        $this->omise->defineApiKeys();
    }

    /**
     * @return void
     */
    public function execute()
    {
         if (! $this->getRequest()->isPost()) {
            // TODO: Only accept for POST verb.
            return;
        }

        $payload = json_decode(file_get_contents('php://input'));

        if ($payload->object !== 'event' || ! $payload->id) {
            // TODO: Handle in case of improper response structure.
            return;
        }

        $event = $this->event->find($payload->id);

        if ($event instanceof Error) {
            // TODO: Handle in case can't retrieve an event object from '$payload->id'.
            return;
        }

        var_dump($event); exit;
    }
}
