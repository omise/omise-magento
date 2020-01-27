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
    protected $awaitingOrderStatus = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;

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
    }

    /**
     * @return Omise_Gateway_Model_Order
     */
    protected function _getOrder()
    {
        $id = $this->getRequest()->getParam('order_id') ?: null;

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
            return $this->paymentAwaiting($order, $charge);
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
    protected function paymentAwaiting($order, $charge) {
        if($charge->isUnauthorized() && $order->getStatus() != Mage_Sales_Model_Order::STATE_CANCELED)
            return $this->cancelOrder($order, $charge);
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

    /**
     * If charge is created and is unauthorized then cancel the order, activate quote. 
     * @param Omise_Gateway_Model_Order $order
     * @param Omise_Gateway_Model_Api_Charge $charge
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function cancelOrder($order, $charge) {
        $session = Mage::getSingleton('checkout/session');
        if ($session->getLastRealOrderId()) {
            $cart = Mage::getSingleton('checkout/cart');
            $incrementId = $session->getLastRealOrderId();
            if (empty($incrementId)) {
                $session->addError($this->__('Your payment failed, Please try again later'));
                $this->_redirect('checkout/cart');
                return;
            }
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            $session->getQuote()->setIsActive(false)->save();
            $session->clear();
            $this->_cancelOrder($order, $session);
            $this->restoreCart($order, $cart, $session);
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * Cancels the order and clears checkout session.
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Checkout_Model_Session $session
     * @return void
     */
    protected function _cancelOrder($order, $session) {
        try {
            $order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_CANCEL, true);
            $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Cancelled order as payment transaction has been cancelled.');
            $order->setStatus(Mage_Sales_Model_Order::STATE_CANCELED);
            $order->cancel()->save();
            $session->unsLastQuoteId()
            ->unsLastSuccessQuoteId()
            ->unsLastOrderId()
            ->unsLastRealOrderId();
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Set all items from $order to $cart.
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Checkout_Model_Cart $cart
     * @param Mage_Checkout_Model_Session $session
     * @return void
     */
    protected function restoreCart($order, $cart, $session) {
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (Mage_Core_Exception $e) {
                $session->addError($this->__($e->getMessage()));
                Mage::logException($e);
                continue;
            }
        }
        $cart->save();
        $session->addNotice($this->__('Cancelled payment transaction.'));
    }
}
