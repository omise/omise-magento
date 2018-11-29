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
            $this->_refresh(OmiseCharge::retrieve($id));
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
            $this->_refresh(OmiseCharge::create($params));
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
            $this->_refresh($this->_object->capture());
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
     * @param  array $params
     *
     * @return Omise_Gateway_Model_Api_Refund|Omise_Gateway_Model_Api_Error
     */
    public function refund($params)
    {
        try {
            return Mage::getModel('omise_gateway/api_refund', $this->_object->refunds()->create($params));
        } catch (Exception $e) {
            return Mage::getModel(
                'omise_gateway/api_error',
                array(
                    'code'    => 'bad_request',
                    'message' => $e->getMessage(),
                )
            );
        }
    }

    /**
     * @param  array $params
     *
     * @return Omise_Gateway_Model_Api_Refund|Omise_Gateway_Model_Api_Error
     */
    public function void($params)
    {
        return $this->refund(array_merge($params, array('void' => true)));
    }

    /**
     * @return Omise_Gateway_Model_Api_Charge|Omise_Gateway_Model_Api_Error
     */
    public function reverse()
    {
        try {
            $this->_refresh($this->_object->reverse());
        } catch (Exception $e) {
            return Mage::getModel(
                'omise_gateway/api_error',
                array(
                    'code'    => 'failed_reverse',
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
        return $this->paid;
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

