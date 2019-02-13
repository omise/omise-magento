<?php
/**
 * Instalment payment info
 */
class Omise_Gateway_Block_Info_Installment extends Mage_Payment_Block_Info {

    /**
     * Prepare instalment related payment info
     *
     * @param Varien_Object|array $transport
     * @return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }

        $transport = parent::_prepareSpecificInformation($transport);

        $data = ['Provider'=>'Instalment provider here...'];

        return $transport->setData(array_merge($data, $transport->getData()));
    }

}
