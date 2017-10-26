<?php
/**
 * @property string $object
 * @property string $id
 * @property bool   $livemode
 * @property string $location
 * @property int    $amount
 * @property string $currency
 * @property string $description
 * @property bool   $capture
 * @property bool   $authorized
 * @property bool   $reversed
 * @property bool   $captured
 * @property string $transaction
 * @property int    $refunded
 * @property array  $refunds
 * @property string $failure_code
 * @property string $failure_message
 * @property array  $card
 * @property string $customer
 * @property string $ip
 * @property string $dispute
 * @property string $created
 *
 * @see      https://www.omise.co/charges-api
 */
class Omise_Gateway_Model_Api_Charge extends Omise_Gateway_Model_Api_Object
{
    /**
     * @param  string $id
     *
     * @return Omise_Gateway_Model_Api_Charge|Omise_Gateway_Model_Api_Error
     */
    public function find($id)
    {
        try {
            $this->refresh(OmiseCharge::retrieve($id));
        } catch (Exception $e) {
            return Mage::getModel(
                'omise_gateway/api_error',
                array(
                    'code'    => 'not_found',
                    'message' => $e->getMessage(),
                )
            );
        }

        return $this;
    }

    /**
     * @param  array $params
     *
     * @return Omise_Gateway_Model_Api_Charge|Omise_Gateway_Model_Api_Error
     */
    public function create($params)
    {
        try {
            $this->refresh(OmiseCharge::create($params));
        } catch (Exception $e) {
            return Mage::getModel(
                'omise_gateway/api_error',
                array(
                    'code'    => 'bad_request',
                    'message' => $e->getMessage(),
                )
            );
        }

        return $this;
    }

    /**
     * @return Omise_Gateway_Model_Api_Charge|Omise_Gateway_Model_Api_Error
     */
    public function capture()
    {
        try {
            $this->refresh($this->object->capture());
        } catch (Exception $e) {
            return Mage::getModel(
                'omise_gateway/api_error',
                array(
                    'code'    => 'failed_capture',
                    'message' => $e->getMessage(),
                )
            );
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthorized()
    {
        return $this->authorized;
    }

    /**
     * @return bool
     */
    public function isUnauthorized()
    {
        return ! $this->isAuthorized();
    }

    /**
     * @return bool
     */
    public function isPaid()
    {
        $paid = isset($this->paid) ? $this->paid : $this->captured;

        return $paid;
    }

    /**
     * @return bool
     */
    public function isUnpaid()
    {
        return ! $this->isPaid();
    }

    /**
     * @return bool
     */
    public function isAwaitCapture()
    {
        return $this->status === 'pending' && $this->isAuthorized() && $this->isUnpaid();
    }

    /**
     * @return bool
     */
    public function isAwaitPayment()
    {
        return $this->status === 'pending' && $this->isUnauthorized() && $this->isUnpaid();
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->status === 'successful' && $this->isPaid();
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }
}

