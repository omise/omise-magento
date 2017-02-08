<?php
class Omise_Gateway_Model_Strategies_CaptureThreeDSecureStrategy extends Omise_Gateway_Model_Strategies_StrategyAbstract
{
    /**
     * {@inheritDoc}
     */
    public function perform($payment, $amount)
    {
        $info = $payment->getPaymentInformation();

        return OmiseCharge::create(array(
            'amount'      => $payment->formatAmount($info->getOrder()->getOrderCurrencyCode(), $amount),
            'currency'    => $info->getOrder()->getOrderCurrencyCode(),
            'description' => 'Charge a card from Magento that order id is ' . $info->getData('entity_id'),
            'capture'     => true,
            'card'        => $info->getAdditionalInformation('omise_token'),
            'return_uri'  => $payment->getThreeDSecureCallbackUri()
        ));
    }

    /**
     * Validate a payment process result.
     *
     * @param  \OmiseCharge $charge
     *
     * @return boolean
     *
     * @see    https://github.com/omise/omise-php/blob/master/lib/omise/OmiseCharge.php
     */
    public function validate($charge)
    {
        if (! isset($charge['authorize_uri'])) {
            $this->message = 'Payment process failed, cannot retrieve a 3-D Secure authorize uri. Please contact our support to confirm the payment.';
            return false;
        }

        $this->message = 'dump error.';
        return false;
    }
}
