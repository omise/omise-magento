<?php
class Omise_Gateway_Model_Api_Object
{
    /**
     * @var mixed
     */
    protected $object;

    /**
     * @param  mixed $object
     *
     * @return self
     */
    protected function refresh($object = null)
    {
        if (is_null($this->object) && is_null($object)) {
            return $this;
        }

        if (! is_null($object)) {
            $this->object = $object;
        } elseif (method_exists($this->object, 'refresh')) {
            $this->object->refresh();
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
        if (isset($this->object[$key])) {
            return $this->object[$key];
        }

        throw new Exception("Error Processing Request", 1);
    }
}
