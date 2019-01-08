<?php
/**
 * @property string $object
 * @property string $id
 * @property bool   $livemode
 * @property string $location
 * @property int    $amount
 * @property string $barcode
 * @property string $currency
 * @property string $email
 * @property string $flow
 * @property int    $installment_terms
 * @property string $name
 * @property string $phone_number
 * @property array  $references
 * @property string $store_id
 * @property string $store_name
 * @property string $terminal_id
 * @property string $type
 *
 * @see      https://www.omise.co/source-api
 */
class Omise_Gateway_Model_Api_Source extends Omise_Gateway_Model_Api_Object {

    /**
     * @param  array $params
     *
     * @return Omise_Gateway_Model_Api_Charge|Omise_Gateway_Model_Api_Error
     */
    public function create($params) {
        try {
            $this->_refresh(OmiseSource::create($params));
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

}