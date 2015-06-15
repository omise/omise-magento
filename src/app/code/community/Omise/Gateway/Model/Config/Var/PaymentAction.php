<?php

class Omise_Gateway_Model_Config_Var_PaymentAction
{
    /**
     * Return an array that use for 'payment action' configuration
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Mage_Payment_Model_Method_Cc::ACTION_AUTHORIZE,
                'label' => Mage::helper('omise_gateway')->__('Authorize only')
            ),
            array(
                'value' => Mage_Payment_Model_Method_Cc::ACTION_AUTHORIZE_CAPTURE,
                'label' => Mage::helper('omise_gateway')->__('Authorize and Capture')
            ),
        );
    }
}
