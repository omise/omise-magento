<?php
class Omise_Gateway_Block_Form_Offsiteinternetbankingpayment extends Mage_Payment_Block_Form
{
    /**
     * Note:
     * Here is an internal constructor (different from real __construct() one).
     * (It's recommened by Magento to override this method instead of __construct()).
     *
     * @see Magento: Mage/Core/Block/Abstract
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('payment/form/omiseoffsiteinternetbankingpayment.phtml');
    }

    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
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
