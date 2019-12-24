<?php
class Omise_Gateway_Callback_ValidateoffsiteinstallmentController extends Omise_Gateway_Controller_Base
{
    const PAYMENT_TITLE = 'Installment payment';
    /**
     * @return Mage_Core_Controller_Varien_Action|void
     * @throws Mage_Core_Exception
     */
    public function indexAction()
    {
        $this->setMessage('The payment has not been confirmed yet.<br/>It may take up to 24 hours to confirm your payment. In case of any questions please don’t hesitate to contact us.');
        return $this->validate();
    }
}
