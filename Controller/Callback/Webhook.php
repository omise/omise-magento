<?php

namespace Omise\Payment\Controller\Callback;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Omise\Payment\Model\Event;
use Magento\Framework\App\Request\Http;

class Webhook extends Action
{
    /**
     * @var \Omise\Payment\Model\Event
     */
    protected $event;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Omise\Payment\Model\Event            $event
     * @param \Magento\Framework\App\Request\Http   $request
     */
    public function __construct(Context $context, Event $event, Http $request)
    {
        $this->event   = $event;
        $this->request = $request;

        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (! $this->event->config->isWebhookEnabled()) {
            return;
        }

        if (! $this->getRequest()->isPost()) {
            // TODO: Only accept for POST verb.
            return;
        }

        $payload = json_decode($this->request->getContent());

        if ($payload->object !== 'event' || ! $payload->id) {
            // TODO: Handle in case of improper response structure.
            return;
        }

        $this->event->handle($payload);
    }
}
