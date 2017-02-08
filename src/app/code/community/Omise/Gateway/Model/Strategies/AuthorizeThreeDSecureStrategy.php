<?php
class Omise_Gateway_Model_Strategies_AuthorizeThreeDSecureStrategy extends Omise_Gateway_Model_Strategies_StrategyAbstract
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
            'capture'     => false,
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
        $this->message = 'dump error.';
        return false;
    }
}
