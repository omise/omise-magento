<?php
class Omise_Gateway_Model_Strategies_RefundStrategy extends Omise_Gateway_Model_Strategies_StrategyAbstract
{
    /**
     * {@inheritDoc}
     */
    public function perform($payment, $amount) {

        $info = $payment->getPaymentInformation();
        $chargeId = $info->getAdditionalInformation('omise_charge_id');
        $charge = OmiseCharge::retrieve($chargeId);

        // TODO - find out if this is an appropriate way of converting the amount to subunits
        // (i.e. Is there a function to do this?)
        $subunitAmount = $amount * 100;

        return $charge->refunds()->create(array('amount' => $subunitAmount));

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
            $this->message = 'Cannot retrieve a refund result, please contact our support to confirm the refund.';
            return false;
        }

        if ($charge['object'] === 'error') {
            $this->message = $charge['message'];
            return false;
        }

        if ($charge['object'] === 'refund') {
            return true;
        }

        $this->message = 'Error refund result validation, please contact our support to confirm the refund.';
        return false;
    }
}
