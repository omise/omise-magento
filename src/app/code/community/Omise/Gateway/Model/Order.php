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
     * @param string $message
     */
    public function markAsFailed($message = null)
    {
        $this
            ->registerCancellation($message, false)
            ->save();

        Mage::getSingleton('core/session')->addError($message);
    }
}

