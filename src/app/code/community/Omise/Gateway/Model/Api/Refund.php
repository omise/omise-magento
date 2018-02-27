<?php
/**
 * @property string $object
 * @property string $id
 * @property string $location
 * @property string $created
 * @property int    $amount
 * @property string $currency
 * @property bool   $voided
 * @property string $charge
 * @property string $transaction
 * @property hash   $metadata
 */
class Omise_Gateway_Model_Api_Refund extends Omise_Gateway_Model_Api_Object
{
    /**
     * @param \OmiseApiResource $resource
     */
    public function __construct($resource)
    {
        if ($resource['object'] !== 'refund') {
            // TODO: Handle error case.
            throw new Exception("Error Processing Request", 1);
        }

        $this->refresh($resource);
    }
}

