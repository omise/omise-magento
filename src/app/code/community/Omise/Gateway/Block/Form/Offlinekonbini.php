<?php
class Omise_Gateway_Block_Form_Offlinekonbini extends Mage_Payment_Block_Form
{
    private $billingAddress;
    
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
        $this->setTemplate('payment/form/omise/omiseofflinekonbini.phtml');
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

    /**
     * Returns billing address stored in checkout session. 
     *
     * @return void
     */
    private function getBillingAddress() {
        if(!isset($this->billingAddress)) {
            $this->billingAddress = Mage::getSingleton('checkout/session')
            ->getQuote()
            ->getBillingAddress();
        }
        return $this->billingAddress;
    }

    /**
     * Returns billing phone number from current quote
     * @return string
     */
    public function getOmiseConvenienceStorePhoneNumber()
    {
        $phoneNumber = $this->getBillingAddress()->getTelephone();
        return (isset($phoneNumber)) ? $phoneNumber : '';
    }

     /**
     * Returns billing email address from current quote
     * @return string
     */
    public function getOmiseConvenienceStoreEmail()
    {
        $email = $this->getBillingAddress()->getEmail();
        return (isset($email)) ? $email : '';
    }

     /**
     * Returns billing customer name from current quote
     * @return string
     */
    public function getOmiseConvenienceStoreCustomerName()
    {
            $name = substr($this->getBillingAddress()->getFirstname(), 0, 10);
        return (isset($name)) ? $name : '';
    }
}