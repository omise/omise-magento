<?php
/**
 * Convenience store  payment info
 */
class Omise_Gateway_Block_Info_Conveniencestore extends Mage_Payment_Block_Info
{
    /**
     * Prepare convenience store related payment info
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
        $info = $this->getInfo()->getAdditionalInformation();
        $data = [ 
            Mage::helper('omise_gateway')->__('Name') => $info['name'],
            Mage::helper('omise_gateway')->__('Email') => $info['email'],
            Mage::helper('omise_gateway')->__('Phone number') => $info['phone_number']
        ];
        return $transport->setData(array_merge($data, $transport->getData()));
    }
}