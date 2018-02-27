<?php
class Omise_Gateway_Model_Observer
{
    public function handleQuoteSubmitFailure($observer)
    {
        $order   = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();

        switch (true) {
            case $payment->getMethodInstance() instanceof Omise_Gateway_Model_Payment_Creditcard:
                $f = fopen('adifninvnifni234nfqwvhvh8', 'a+');
                fwrite($f, 'credit card');
                fclose($f);
                break;

            default:
                $f = fopen('adifninvnifni234nfqwvhvh8', 'a+');
                fwrite($f, 'something else');
                fclose($f);
                break;
        }
    }
}
