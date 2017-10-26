<?php
class Omise_Gateway_Model_Event_Charge_Complete
{
    /**
     * @var string  of an event name.
     */
    public $event = 'charge.complete';

    /**
     * There are several cases with the following payment methods
     * that would trigger the 'charge.complete' event.
     *
     * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
     * Alipay
     * charge data in payload:
     *     [status: 'successful'], [authorized: 'true'], [paid: 'true']
     *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
     *
     * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
     * Internet Banking
     * charge data in payload:
     *     [status: 'successful'], [authorized: 'true'], [paid: 'true']
     *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
     *
     * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
     * Credit Card (3-D Secure)
     * CAPTURE = FALSE
     * charge data in payload could be one of these sets:
     *     [status: 'pending'], [authorized: 'true'], [paid: 'false']
     *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
     *
     * CAPTURE = TRUE
     * charge data in payload could be one of these sets:
     *     [status: 'successful'], [authorized: 'true'], [paid: 'true']
     *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
     *
     * @param  Omise_Gateway_Model_Api_Event $event
     *
     * @return void
     */
    public function handle($event)
    {
        echo "COMPLETE";

        return;
    }
}
