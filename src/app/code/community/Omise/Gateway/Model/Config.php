<?php
class Omise_Gateway_Model_Config extends Mage_Core_Model_Abstract
{

    /**
     * Note:
     * Here is an internal constructor (different from real __construct() one).
     * (It's recommened by Magento to override this method instead of __construct()).
     *
     * @see Magento: lib/Varien/Object.php
     */
    protected function _construct()
    {
        $this->_init('omise_gateway/config');
    }

    /**
     * Retrieve array of internet banking banks
     *
     * @return array
     */
    public function getInternetBankingBanks()
    {
        $_types = Mage::getConfig()->getNode('global/payment/omise/internet_banking')->asArray();
        $types = [];
        foreach ($_types as $code => $data) {
            $types[$code] = $data['name'];
        }
        return $types;
    }

    /**
     * Retrieve array of instalment payment providers
     *
     * @return array
     */
    public function getInstallmentProviders()
    {
        $_types = Mage::getConfig()->getNode('global/payment/omise/installment')->asArray();
        $types = [];
        foreach ($_types as $code => $data) {
            $types[$code] = $data;
            if (!array_key_exists('interest_rate', $data)) {
                $types[$code]['interest_rate'] = Mage::getConfig()->getNode('default/payment/omise_offsite_installment/default_interest_rate');
            }
        }
        return $types;
    }
}
