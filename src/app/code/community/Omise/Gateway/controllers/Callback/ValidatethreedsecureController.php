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

        try {
            $charge_id = $payment->getMethodInstance()->getInfoInstance()->getAdditionalInformation('omise_charge_id');
            $charge    = OmiseCharge::retrieve($charge_id);

            if (! $this->validate($charge)) {
                return $this->markOrderAsFailed(
                    $order,
                    $this->__('The payment was invalid, ' . $charge['failure_message'] . ' (' . $charge['failure_code'] . ').')
                );
            }

            $payment->accept();
            $order->save();
            return $this->_redirect('checkout/onepage/success');
        } catch (Exception $e) {
            return $this->markOrderAsFailed(
                $order,
                $this->__($e->getMessage())
            );
        }
    }

    /**
     * @param  \OmiseCharge $charge
     *
     * @return bool
     */
    protected function validate($charge)
    {
        // check for auto capture.
        if ($charge['capture'] && $charge['status'] === 'successful' && $charge['authorized'] && $charge['captured']) {
            return true;
        }

        // Check for authorize only.
        if (! $charge['capture'] && $charge['status'] === 'pending' && $charge['authorized']) {
            return true;
        }

        return false;
    }
}
