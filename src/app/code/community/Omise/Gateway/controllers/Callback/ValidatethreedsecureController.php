<?php
class Omise_Gateway_Callback_ValidatethreedsecureController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $omise = Mage::getModel('omise_gateway/omise');
        $omise->initNecessaryConstant();

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
                return $this->considerFail(
                    $order,
                    $this->__('The payment was invalid, ' . $charge['failure_message'] . ' (' . $charge['failure_code'] . ').')
                );
            }

            $payment->accept();
            $order->save();
            return $this->_redirect('checkout/onepage/success');
        } catch (Exception $e) {
            return $this->considerFail(
                $order,
                $this->__($e->getMessage())
            );
        }
    }

    /**
     * @return \Mage_Sales_Model_Order
     */
    protected function getOrder()
    {
        $order_increment_id = $this->getRequest()->getParam('order_id');

        if ($order_increment_id) {
            return Mage::getModel('sales/order')->loadByIncrementId($order_increment_id);
        }

        return Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
    }

    /**
     * @param  \Mage_Sales_Model_Order $order
     * @param  string                  $message
     *
     * @return self
     */
    protected function considerFail($order, $message)
    {
        $order->getPayment()
            ->setPreparedMessage($message)
            ->deny();
        $order->save();

        Mage::getSingleton('core/session')->addError($message);
        return $this->_redirect('checkout/cart');
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
