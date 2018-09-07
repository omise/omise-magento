<?php
/**
 * @property string $object
 * @property string $id
 * @property bool   $livemode
 * @property string $location
 * @property string $key
 * @property string $created
 * @property Object $data
 * @see      https://www.omise.co/events-api
 */
class Omise_Gateway_Model_Api_Event extends Omise_Gateway_Model_Api_Object
{
    /**
     * @param  string $id
     *
     * @return Omise_Gateway_Model_Api_Event|Omise_Gateway_Model_Api_Error
     */
    public function find($id)
    {
        try {
            $event         = OmiseEvent::retrieve($id);
            $event['data'] = $this->_transformDataToObject($event['data']);

            $this->_refresh($event);
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
     * @param  json-object $data
     *
     * @return Omise_Gateway_Model_Api_Object|json-object
     */
    protected function _transformDataToObject($data)
    {
        switch ($data['object']) {
            case 'charge':
                $data = Mage::getModel('omise_gateway/api_charge')->find($data['id']);
                break;
        }

        return $data;
    }
}

