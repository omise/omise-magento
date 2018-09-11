<?php
class Omise_Gateway_Model_Order extends Mage_Sales_Model_Order
{
    /**
     * @param  string $id
     *
     * @return self
     */
    public function getOrder($id = null)
    {
        if ($id) {
            return $this->loadByIncrementId($id);
        }

        return $this->loadBySession();
    }

    /**
     * @return self
     */
    public function loadBySession()
    {
        $this->load(Mage::getSingleton('checkout/session')->getLastOrderId());

        return $this;
    }

    /**
     * @param string $transaction_id
     * @param string $state
     * @param string $message
     */
    public function markAsAwaitPayment($transaction_id, $state = null, $message = null)
    {
        $this
            ->setState(($state ? $state : Mage_Sales_Model_Order::STATE_PROCESSING), true, $message)
            ->save();
    }

    /**
     * @param string $transaction_id
     */
    public function isInvoicePaid($transaction_id)
    {
        return $this->getInvoice($transaction_id)->getState() == Mage_Sales_Model_Order_Invoice::STATE_PAID;
    }

    /**
     * @param string $transaction_id
     * @param string $state
     * @param string $message
     */
    public function markAsPaid($transaction_id, $state = null, $message = null)
    {
        if ($invoice = $this->getInvoice($transaction_id)) {
            $invoice->pay();
            $invoice->setIsPaid(true);

            $this->addRelatedObject($invoice);
        }

        $payment     = $this->getPayment();
        $transaction = $payment->getTransaction($payment->getLastTransId());
        if ($transaction->getTxnType() === Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE) {
            $transaction->closeCapture();
        } else {
            $transaction->close();
        }

        $this
            ->addRelatedObject($transaction)
            ->setState(($state ? $state : Mage_Sales_Model_Order::STATE_PROCESSING), true, $message)
            ->save();
    }

    /**
     * @param string $transaction_id
     * @param string $message
     */
    public function markAsFailed($transaction_id, $message = null)
    {
        if ($invoice = $this->getInvoice($transaction_id)) {
            $invoice->cancel();

            $this->addRelatedObject($invoice);
        }

        $payment     = $this->getPayment();
        $transaction = $payment->getTransaction($payment->getLastTransId());
        if ($transaction->getTxnType() === Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE) {
            $transaction->closeCapture();
        } else {
            $transaction->close();
        }

        $this
            ->registerCancellation($message, false)
            ->addRelatedObject($transaction)
            ->save();

        Mage::getSingleton('core/session')->addError($message);
    }

    /**
     * @param  string $transaction_id
     *
     * @return \Mage_Sales_Model_Order_Invoice
     */
    public function getInvoice($transaction_id)
    {
        foreach ($this->getInvoiceCollection() as $invoice) {
            if ($invoice->getTransactionId() == $transaction_id) {
                $invoice->load($invoice->getId());

                return $invoice;
            }
        }

        return;
    }
}

