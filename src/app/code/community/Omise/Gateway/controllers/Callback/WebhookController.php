<?php
class Omise_Gateway_Callback_WebhookController extends Omise_Gateway_Controllers_Callback_Base
{
    public function indexAction()
    {
        if (! $this->getRequest()->isPost()) {
            return $this->norouteAction();
        }

        $payload = json_decode(file_get_contents('php://input'));

        if ($payload->object !== 'event' || ! $payload->id) {
            return $this->norouteAction();
        }

        $event = Mage::getModel('omise_gateway/api_event')->find($payload->id);
        if ($event instanceof Omise_Gateway_Model_Api_Error) {
            return; $this->norouteAction();
        }

        $result = Mage::getModel('omise_gateway/event')->handle($event);
    }
}
