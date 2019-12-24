<?php
abstract class Omise_Gateway_Controller_Base extends Mage_Core_Controller_Front_Action
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $transId;

    /**
     * @var Omise_Gateway_Model_Order
     */
    protected $order;

    /**
     * @var string
     */
    protected $awaitingOrderStatus;

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * constructor
     */
    protected function _construct()
    {
        $omise = Mage::getModel('omise_gateway/omise');
        $omise->initNecessaryConstant();
        $this->awaitingOrderStatus = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
    }

    /**
     * @return Omise_Gateway_Model_Order
     */
    protected function _getOrder()
    {
        $id = $this->getRequest()->getParam('order_id') ? $this->getRequest()->getParam('order_id') : null;

        return Mage::getModel('omise_gateway/order')->getOrder($id);
    }

    /**
     * @return Mage_Core_Controller_Varien_Action|void
     * @throws Mage_Core_Exception
     */
    protected function validate() {
        if(!isset($this->order))
            $this->order = $this->_getOrder();
        if (! $payment = $this->order->getPayment()) {
            Mage::getSingleton('core/session')->addError(
                $this->__('%s validation failed, we cannot retrieve your payment information. 
                Please contact our support team to confirm the payment.', $this::PAYMENT_TITLE
                )
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
        $this->transId = $payment->getLastTransId();
        return $this->checkPaymentStatusAndUpdateOrder($charge, $this->order);
    }

    /**
     * Checks payment status from $charge and updates order status accordingly.
     * @param Omise_Gateway_Model_Api_Charge $charge
     * @param Omise_Gateway_Model_Order $order
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function checkPaymentStatusAndUpdateOrder($charge, $order) {
        if ($charge->isAwaitCapture() || $charge->isAwaitPayment()) {
            return $this->paymentAwaiting($order);
        }
        if ($charge->isSuccessful()) {
            return $this->paymentSuccessful($order);
        }
        $order->markAsFailed(
            $this->transId,
            $this->__('The payment was invalid, %s (%s)', $charge->failure_message, $charge->failure_code)
        );
        return $this->_redirect('checkout/cart');
    }

    /**
     * If payment is awaiting to capture then updating order status as 'payment_review'. In case of 3-D secured payment,
     * it will be 'processing'.
     * @param Omise_Gateway_Model_Order $order
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function paymentAwaiting($order) {
        $message = (empty($this->getMessage()))
            ? 'The payment is in progress.<br/>Due to the way %s works, this might take a few seconds or up to an hour. Please click "Accept" or "Deny" to complete the transaction process'
            : $this->getMessage();
        $order->markAsAwaitPayment(
            $this->transId,
            $this->awaitingOrderStatus,
            Mage::helper('omise_gateway')->__($message, $this::PAYMENT_TITLE)
        );
        if($this->awaitingOrderStatus != Mage_Sales_Model_Order::STATE_PROCESSING) {
            Mage::getSingleton('checkout/session')
                ->addNotice(Mage::helper('omise_gateway')
                    ->__('Please note - the payment process is still ongoing. Once it is complete, you will receive the 
                order confirmation.')
                );
        }
        return $this->_redirect('checkout/onepage/success');
    }

    /**
     * If payment has capture successfully then update order status as 'processing'
     * @param Omise_Gateway_Model_Order $order
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function paymentSuccessful($order) {
        $invoice = $order->getInvoice($this->transId);
        // Make sure to avoid marking invoice paid more than once
        if (!$order->isInvoicePaid($this->transId)) $order->markAsPaid(
            $this->transId,
            Mage_Sales_Model_Order::STATE_PROCESSING,
            Mage::helper('omise_gateway')
                ->__('An amount of %s has been paid online.',
                    $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal())
                )
        );
        $order->sendNewOrderEmail();
        return $this->_redirect('checkout/onepage/success');
    }
}
