<?php
class Omise_Gateway_Model_Api_List extends Omise_Gateway_Model_Api_Object
{
    /**
     * @param \OmiseApiResource $resource
     */
    public function __construct($resource)
    {
        if ($resource['object'] === 'list') {
            $this->refresh($object);
        }
    }
}
