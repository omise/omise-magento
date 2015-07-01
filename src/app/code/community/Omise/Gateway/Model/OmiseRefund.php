<?php
class Omise_Gateway_Model_OmiseRefund extends Omise_Gateway_Model_Omise
{

    /**
     * Creates a new charge with Omise Payment Gateway.
     * @param array $params
     * @return OmiseCharge|Exception
     */
    public function createOmiseRefund($params)
    {
        $transactionId = $params['transaction_id'];
        $amount = $params['amount'];
        
        try {
            $charge = OmiseCharge::retrieve($transactionId, $this->_public_key, $this->_secret_key);
            $refunds = new OmiseRefundList($charge->refunds(), $transactionId, $this->_public_key, $this->_secret_key);
            $refund = $refunds->create(array('amount' => $amount));
            return $refund;
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }
}