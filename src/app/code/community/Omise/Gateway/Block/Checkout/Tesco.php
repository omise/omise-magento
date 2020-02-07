<?php
class Omise_Gateway_Block_Checkout_Tesco extends Mage_Checkout_Block_Onepage_Success
{
    /**
     * Calls OmiseCharge api to retrieve charge by charge ID, returns false if charge not found.
     * @return mixed
     */
    public function getCharge() {
        $order = Mage::helper('omise_gateway')->getLastCheckedoutOrder();
        $payment = $order->getPayment();
        if ($order->hasInvoices()) {
            $charge = Mage::getModel('omise_gateway/api_charge')->find(
                $payment->getMethodInstance()->getInfoInstance()->getAdditionalInformation('omise_charge_id')
            );
            return $charge; 
        }
        return false;
    }

    /**
     * Calls function from helper to convert Barcode SVG to HTML.
     * @param Omise_Gateway_Model_Api_Charge $charge
     * @return string
     */
    public function tescoBarcodeSvgToHtml($charge) {
        return Mage::helper('omise_gateway')->tescoBarcodeSvgToHtml($charge);
    }
    
    /**
     * Calling function from helper combines reference numbers entities into one string.
     * @param Omise_Gateway_Model_Api_Charge $charge
     * @return string
     */
    public function generateTescoReference($charge) {
        return Mage::helper('omise_gateway')->generateTescoReference($charge);
    }
}