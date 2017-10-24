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
class Omise_Gateway_Model_Api_Charge
{
    protected $object;

    /**
     * @param  string $id
     *
     * @return Omise_Gateway_Model_Api_Charge|array
     */
    public function find($id)
    {
        try {
            $this->object = OmiseCharge::retrieve($id);
        } catch (Exception $e) {
            return array(
                'object'  => 'error',
                'code'    => 'not_found',
                'message' => $e->getMessage(),
            );
        }

        return $this;
    }

    /**
     * @param  array $params
     *
     * @return Omise_Gateway_Model_Api_Charge|array
     */
    public function create($params)
    {
        try {
            $this->object = OmiseCharge::create($params);
        } catch (Exception $e) {
            return array(
                'object'  => 'error',
                'code'    => 'bad_request',
                'message' => $e->getMessage(),
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
    public function isAwaitForCapture()
    {
        return $this->status === 'pending' && $this->isAuthorized() && $this->isUnpaid();
    }

    /**
     * @return bool
     */
    public function isAwaitForPayment()
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

    public function __get($key)
    {
        if (isset($this->object[$key])) {
            return $this->object[$key];
        }

        throw new Exception("Error Processing Request", 1);
    }
}

