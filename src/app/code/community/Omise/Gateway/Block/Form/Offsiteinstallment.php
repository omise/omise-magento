<?php
class Omise_Gateway_Block_Form_Offsiteinstallment extends Mage_Payment_Block_Form
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
        $this->setTemplate('payment/form/omise/omiseoffsiteinstallment.phtml');
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
     * Has the merchant selected interest free installmnet payments for merchants?
     *
     * @return boolean
     */
    public function isInterestFree()
    {
        return Mage::getModel('omise_gateway/api_capabilities')->isZeroInterestInstallments();
    }

    /**
     * Calculate monthly payment amount for installment backend, given number of months
     *
     * @return boolean
     */
    public function getMonthlyPaymentAmount($saleAmount, $monthCount, $interestRatePC)
    {
        $interestRate = $this->isInterestFree() ? 0 : $interestRatePC/100;
        var_dump($interestRate);
        $VAT = 0.07; // Should this be in config, and also country dependent??
        $interestAmount = $interestRate * $monthCount * $saleAmount * (1+$VAT);
        $totalAmount = $saleAmount + $interestAmount;
        return $totalAmount/$monthCount;
    }

    /**
     * Gets an array of available installment backends (with some conveniences added for building HTML)
     *
     * @return [backends]
     */
    public function getInstallmentBackends()
    {
        $backends = Mage::getModel('omise_gateway/payment_offsiteinstallment')->getValidBackends();
 
        foreach ($backends as &$backend) {
            $backend->_bankcode = str_replace('installment_', '', $backend->_id);
            $backend->_bankname = Mage::helper('omise_gateway')->installmentProviderName($backend->_id);
            $backend->_interest_rate = Mage::helper('omise_gateway')->installmentProviderInterestRate($backend->_id);
        }
        return $backends;
    }
}
