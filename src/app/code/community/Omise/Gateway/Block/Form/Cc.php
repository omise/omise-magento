<?php
class Omise_Gateway_Block_Form_Cc extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('payment/form/omisecc.phtml');
    }

    /**
     * Retrieve payment configuration object
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }

    /**
     * Retrieve Omise keys from database
     * @return string|array
     */
    public function getOmiseKeys($omise_key = '')
    {
        // Create a new model instance and query data from 'omise_gateway' table.
        $config = Mage::getModel('omise_gateway/config')->load(1);
        $data   = array(
            'public_key' => $config->public_key,
            'secret_key' => $config->secret_key
        );

        if ($config->test_mode) {
            $data['public_key'] = $config->public_key_test;
            $data['secret_key'] = $config->secret_key_test;
        }

        if ($omise_key == '')
            return $data;
        else
            return isset($data[$omise_key]) ? $data[$omise_key] : '';
    }

    /**
     * Retrieve availables credit card types
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = $this->_getConfig()->getCcTypes();
        if ($method = $this->getMethod()) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code=>$name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }
        return $types;
    }

    /**
     * Retrieve credit card expire months
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
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = $this->_getConfig()->getYears();
            $years = array(0=>$this->__('Year'))+$years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

    /**
     * Retrive has verification configuration
     * @return boolean
     */
    public function hasVerification()
    {
        if ($this->getMethod()) {
            $configData = $this->getMethod()->getConfigData('useccv');
            if(is_null($configData)){
                return true;
            }
            return (bool) $configData;
        }
        return true;
    }

    /**
     * Whether switch/solo card type available
     * @return boolean
     */
    public function hasSsCardType()
    {
        $availableTypes = explode(',', $this->getMethod()->getConfigData('cctypes'));
        $ssPresenations = array_intersect(array('SS', 'SM', 'SO'), $availableTypes);
        if ($availableTypes && count($ssPresenations) > 0) {
            return true;
        }
        return false;
    }

    /**
     * solo/ switch card start year
     * @return array
     */
     public function getSsStartYears()
    {
        $years = array();
        $first = date("Y");

        for ($index=5; $index>=0; $index--) {
            $year = $first - $index;
            $years[$year] = $year;
        }
        $years = array(0=>$this->__('Year'))+$years;
        return $years;
    }

    /**
     * Render block HTML
     * @return string
     */
    protected function _toHtml()
    {
        Mage::dispatchEvent('payment_form_block_to_html_before', array(
            'block'     => $this
        ));
        return parent::_toHtml();
    }
}
