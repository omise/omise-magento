<?php
class Omise_Gateway_Model_Strategies_AuthorizeStrategy extends Omise_Gateway_Model_Strategies_StrategyAbstract
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
            'card'        => $info->getAdditionalInformation('omise_token')
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
        if (! isset($charge['object'])) {
            $this->message = 'Cannot retrieve a payment result, please contact our support to confirm the payment.';
            return false;
        }

        if ($charge['object'] === 'error') {
            $this->message = $charge['message'];
            return false;
        }

        if (! $charge['authorized']) {
            $this->message = 'Payment authorization failed, ' . $charge['failure_message'] . ' (code: ' . $charge['failure_code'] . ')';
            return false;
        }

        if ($charge['object'] === 'charge'
            && $charge['status'] === 'pending'
            && $charge['capture'] === false
            && $charge['authorized'] === true) {
            return true;
        }

        $this->message = 'Error payment result validation, please contact our support to confirm the payment.';
        return false;
    }
}
