<?php
class Omise_Gateway_Model_Config_Var_Currency
{
    /**
     * Return an array that use for 'currency' configuration
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'thb',
                'label' => 'THB'
            )
        );
    }
}
