<?php
class Omise_Gateway_Model_Config_Var_Cctype extends Mage_Payment_Model_Source_Cctype
{
    /**
     * {@inheritDoc}
     *
     * @see app/code/core/Mage/Payment/Model/Source/Cctype.php
     */
    public function getAllowedTypes()
    {
        return array('VI', 'MC', 'AE', 'JCB');
    }
}
