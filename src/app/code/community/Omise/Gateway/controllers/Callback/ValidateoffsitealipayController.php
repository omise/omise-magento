<?php
class Omise_Gateway_Callback_ValidateoffsitealipayController extends Omise_Gateway_Controller_Base
{
    public function indexAction()
    {
        // Callback validation.
        $order = $this->getOrder();

        if (! $payment = $order->getPayment()) {
            Mage::getSingleton('core/session')->addError(
                $this->__('Alipay validation was invalid, cannot retrieve your payment information. Please contact our support to confirm the payment.')
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

        if ($charge->isAwaitPayment()) {
            $order->markAsAwaitPayment(
                $payment->getLastTransId(),
                Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW,
                Mage::helper('omise_gateway')->__('The payment has been processing.<br/>Due to the Alipay process, this might takes a few seconds or up-to an hour. Please click "Accept" or "Deny" the payment manually once the result has been updated (you can check at Omise Dashboard).')
            );
            Mage::getSingleton('checkout/session')->addNotice(Mage::helper('omise_gateway')->__('Please note - the payment process is still ongoing. Once it is complete, you will receive the order confirmation.'));
            return $this->_redirect('checkout/onepage/success');
        }

        if ($charge->isSuccessful()) {
            $invoice = $order->getInvoice($payment->getLastTransId());

            $order->markAsPaid(
                $payment->getLastTransId(),
                Mage_Sales_Model_Order::STATE_PROCESSING,
                Mage::helper('omise_gateway')->__('An amount %s has been paid online.', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
            );

            return $this->_redirect('checkout/onepage/success');
        }

        $order->markAsFailed(
            $payment->getLastTransId(),
            $this->__('The payment was invalid, %s (%s)', $charge->failure_message, $charge->failure_code)
        );

        return $this->_redirect('checkout/cart');
    }
}
