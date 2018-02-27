<?php
class Omise_Gateway_Model_Observer
{
    public function __construct()
    {
        $omise = Mage::getModel('omise_gateway/omise');
        $omise->initNecessaryConstant();
    }

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
                $charge = $this->retrieveCharge(
                    $payment->getMethodInstance()->getInfoInstance()->getAdditionalInformation('omise_charge_id')
                );

                if ($charge->isAwaitCapture()) {
                    $charge->reverse();
                } else if ($charge->isSuccessful()) {
                    $charge->refunds()->create(array('amount' => $charge->amount));
                }
                break;
        }
    }

    /**
     * @param  string $id
     *
     * @return Omise_Gateway_Model_Api_Charge|Omise_Gateway_Model_Api_Error
     */
    private function retrieveCharge($id)
    {
        return Mage::getModel('omise_gateway/api_charge')->find($id);
    }
}
