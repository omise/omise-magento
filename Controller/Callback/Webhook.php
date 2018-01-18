<?php

namespace Omise\Payment\Controller\Callback;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Omise\Payment\Model\Event;
use Omise\Payment\Model\Api\Error as ApiError;

class Webhook extends Action
{
    /**
     * @var \Omise\Payment\Model\Event
     */
    protected $event;

    public function __construct(Context $context, Event $event)
    {
        $this->event = $event;

        parent::__construct($context);
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

        if ($event instanceof ApiError) {
            // TODO: Handle in case can't retrieve an event object from '$payload->id'.
            return;
        }

        $result = $this->event->handle($event);
    }
}
