<?php
class Omise_Gateway_Block_Form_Offsiteinstallment extends Mage_Payment_Block_Form {

    static protected $_banknames = [
        'bay'             => 'Krungsri',
        'first_choice'    => 'First Choice',
        'kbank'           => 'KBank',
        'bbl'             => 'Bangkok Bank',
        'ktc'             => 'KTC',
    ];

    /**
     * Preparing global layout
     * You can redefine this method in child classes for changing layout
     *
     * @return Mage_Core_Block_Abstract
     *
     * @see    Mage_Core_Block_Abstract
     */
    protected function _prepareLayout() {
        $this->setTemplate('payment/form/omise/omiseoffsiteinstallment.phtml');
        return $this;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml() {
        Mage::dispatchEvent(
            'payment_form_block_to_html_before',
            array('block' => $this)
        );
        return parent::_toHtml();
    }

    /**
     * Gets an array of available installment backends (with some conveniences added for building HTML)
     *
     * @return [backends]
     */
    public function getInstallmentBackends() {
        $backends = Mage::getModel('omise_gateway/payment_offsiteinstallment')->getValidBackends();
        foreach ($backends as &$backend) {
            $backend->_bankcode = str_replace('installment_', '', $backend->_id);
            $backend->_bankname = self::$_banknames[$backend->_bankcode];
        }
        return $backends;
    }

}
