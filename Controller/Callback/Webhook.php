<?php

namespace Omise\Payment\Controller\Callback;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Omise\Payment\Model\Omise;
use Omise\Payment\Model\Api\Event as ApiEvent;
use Omise\Payment\Model\Event\Charge\Complete as EventChargeComplete;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Omise\Payment\Model\Config\Config;

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

    private $supportedEvents = [
        'charge.capture',
        'refund.create'
    ];

    /**
     * @var \Omise\Payment\Model\Api\Event
     */
    protected $apiEvent;

    /**
     * @var EventManager
     */
    private $eventManager;

    private $config;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Omise\Payment\Model\Omise $omise
     * @param \Omise\Payment\Model\Event $apiEvent
     * @param \Magento\Framework\Event\ManagerInterface
     * @param \Omise\Payment\Model\Config\Config $config
     */
    public function __construct(
        Context $context,
        Http $request,
        Omise $omise,
        ApiEvent $apiEvent,
        EventManager $eventManager,
        Config $config
    ) {
        $this->request = $request;
        $this->apiEvent = $apiEvent;
        $this->eventManager = $eventManager;
        $this->config = $config;

        $omise->defineUserAgent();
        $omise->defineApiVersion();
        $omise->defineApiKeys();

        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (! $this->config->isWebhookEnabled()) {
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

        $event = $this->apiEvent->find($payload->id);

        if (! $event instanceof ApiEvent) {
            // TODO: Handle in case can't retrieve an event object from '$payload->id'.
            return;
        }

        if (!in_array($event->key, $this->supportedEvents)) {
            // TODO: Handle in case can't retrieve an event object from '$payload->id'.
            return;
        }

        $eventType = 'omise_payment_webhook_' . str_replace(".", "_", $event->key);

        $this->eventManager->dispatch($eventType, ['data' => $event->data]);
    }
}
