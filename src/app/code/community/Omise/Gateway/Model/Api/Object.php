<?php
class Omise_Gateway_Model_Api_Object
{
    /**
     * @var mixed
     */
    protected $_object;

    /**
     * @param  mixed $object
     *
     * @return self
     */
    protected function _refresh($object = null)
    {
        if (is_null($this->_object) && is_null($object)) {
            return $this;
        }

        if (! is_null($object)) {
            $this->_object = $object;
        } elseif (method_exists($this->_object, 'refresh')) {
            $this->_object->refresh();
        }

        return $this;
    }

    /**
     * @param  string $key
     *
     * @throws Exception
     */
    public function __get($key)
    {
        if (isset($this->_object[$key])) {
            return $this->_object[$key];
        }

        throw new Exception("Error Processing Request", 1);
    }
}
