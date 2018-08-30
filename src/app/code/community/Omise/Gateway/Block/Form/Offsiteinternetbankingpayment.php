<?php
class Omise_Gateway_Block_Form_Offsiteinternetbankingpayment extends Mage_Payment_Block_Form
{

    /**
     * Preparing global layout
     * You can redefine this method in child classes for changing layout
     *
     * @return Mage_Core_Block_Abstract
     *
     * @see    Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('payment/form/omise/omiseoffsiteinternetbankingpayment.phtml');
        return $this;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        Mage::dispatchEvent(
            'payment_form_block_to_html_before',
            array('block' => $this)
        );

        return parent::_toHtml();
    }
}
