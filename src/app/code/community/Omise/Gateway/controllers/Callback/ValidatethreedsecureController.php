<?php
class Omise_Gateway_Callback_ValidatethreedsecureController extends Omise_Gateway_Controllers_Callback_Base
{
    public function indexAction()
    {
        // Callback validation.
        $order = $this->getOrder();

        if (! $payment = $order->getPayment()) {
            Mage::getSingleton('core/session')->addError(
                $this->__('3-D Secure validation was invalid, cannot retrieve your payment information. Please contact our support to confirm the payment.')
            );
            $this->_redirect('checkout/cart');
            return;
        }

        $charge = Mage::getModel('omise_gateway/api_charge')->find(
            $payment->getMethodInstance()->getInfoInstance()->getAdditionalInformation('omise_charge_id')
        );

        if ($charge instanceof Omise_Gateway_Model_Api_Error) {
            Mage::getSingleton('core/session')->addError($charge->getMessage());

            return $this->_redirect('checkout/cart');
        }

        if ($charge->isAwaitCapture() || $charge->isSuccessful()) {
            $payment->accept();
            $order->save();

            return $this->_redirect('checkout/onepage/success');
        }

        return $this->markOrderAsFailed(
            $order,
            $this->__('The payment was invalid, ' . $charge->failure_message . ' (' . $charge->failure_code . ').')
        );
    }
}
