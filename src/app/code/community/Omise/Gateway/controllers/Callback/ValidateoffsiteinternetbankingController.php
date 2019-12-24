<?php
class Omise_Gateway_Callback_ValidateoffsiteinternetbankingController extends Omise_Gateway_Controller_Base
{
    const PAYMENT_TITLE = 'Bank Payment';
    /**
     * @return Mage_Core_Controller_Varien_Action|void
     * @throws Mage_Core_Exception
     */
    public function indexAction()
    {
        $this->setMessage('The payment is in progress.<br/>Due to the bank\'s processing, this might take a few seconds or up-to an hour. Please click "Accept" or "Deny" to complete the payment manually once the result has been updated (you can check at Omise Dashboard).');
        return $this->validate();
    }
}
