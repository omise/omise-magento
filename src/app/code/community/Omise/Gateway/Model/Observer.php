<?php
class Omise_Gateway_Model_Observer
{
    public function __construct()
    {
        $omise = Mage::getModel('omise_gateway/omise');
        $omise->initNecessaryConstant();
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function handleQuoteSubmitFailure($observer)
    {
        $order   = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();

        switch (true) {
            case $payment->getMethodInstance() instanceof Omise_Gateway_Model_Payment_Offsitealipay:
            case $payment->getMethodInstance() instanceof Omise_Gateway_Model_Payment_Offsiteinternetbanking:
                // Do nothing.
                break;

            case $payment->getMethodInstance() instanceof Omise_Gateway_Model_Payment_Creditcard:
                if (! $payment->getMethodInstance()->getInfoInstance()->getAdditionalInformation('omise_charge_id')) {
                    return;
                }

                $charge = Mage::getModel('omise_gateway/api_charge')->find(
                    $payment->getMethodInstance()->getInfoInstance()->getAdditionalInformation('omise_charge_id')
                );

                if ($charge->isAwaitCapture()) {
                    $charge->reverse();
                } else if ($charge->isSuccessful()) {
                    $charge->void(array('amount' => $charge->amount));
                }
                break;
        }
    }
}
