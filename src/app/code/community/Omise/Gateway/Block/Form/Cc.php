<?php
class Omise_Gateway_Block_Form_Cc extends Mage_Payment_Block_Form
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
        if ($this->isApplicable()) {
            $this->setTemplate('payment/form/omisecc.phtml');
        } else {
            $this->setTemplate('payment/form/omise-inapplicable-method.phtml');
        }

        return $this;
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
     * Check if the payment method is applicable for the checkout form.
     *
     * @return bool
     */
    protected function isApplicable()
    {
        if (! $this->isStoreCurrencySupported()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function isStoreCurrencySupported()
    {
        $currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();

        switch ($currencyCode) {
            case 'THB':
            case 'JPY':
            case 'IDR':
            case 'SGD':
                return true;
                break;
        }

        return false;
    }

    /**
     * Whether the One Step Checkout Support option is enabled
     *
     * @return bool
     */
    public function isOscSupportEnabled()
    {
        return Mage::getModel('omise_gateway/paymentMethod')->isOscSupportEnabled();
    }

    /**
     * Retrieve Omise keys from database
     *
     * @return string|array
     */
    public function getOmiseKeys($omise_key = '')
    {
        // Create a new model instance and query data from 'omise_gateway' table.
        $config = Mage::getModel('omise_gateway/config')->load(1);

        if ($config->test_mode) {
            $data['public_key'] = $config->public_key_test;
            $data['secret_key'] = $config->secret_key_test;
        } else {
            $data['public_key'] = $config->public_key;
            $data['secret_key'] = $config->secret_key;
        }

        if ($omise_key == '') {
            return $data;
        }

        return isset($data[$omise_key]) ? $data[$omise_key] : '';
    }

    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = $this->_getConfig()->getCcTypes();
        if ($method = $this->getMethod()) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code => $name) {
                    if (! in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }
        return $types;
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] =  $this->__('Month');
            $months = array_merge($months, $this->_getConfig()->getMonths());
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = $this->_getConfig()->getYears();
            $years = array(0 => $this->__('Year')) + $years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

    /**
     * Retrieve has verification configuration
     *
     * @return boolean
     */
    public function hasVerification()
    {
        return true;
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
