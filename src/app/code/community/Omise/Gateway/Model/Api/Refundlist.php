<?php
class Omise_Gateway_Model_Api_Refundlist extends Omise_Gateway_Model_Api_List
{
    /**
     * @param  array $params
     *
     * @return Omise_Gateway_Model_Api_Refund|Omise_Gateway_Model_Api_Error
     */
    public function create($params)
    {
        try {
            return Mage::getModel('omise_gateway/api_refund', $this->object->create($params));
        } catch (Exception $e) {
            return Mage::getModel(
                'omise_gateway/api_error',
                array(
                    'code'    => 'bad_request',
                    'message' => $e->getMessage(),
                )
            );
        }
    }
}
