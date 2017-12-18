<?php

namespace Omise\Payment\Model\Api;

use Exception;

class Object
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
     * @deprecated  This will be used for temporary while refactoring code.
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param  string $key
     *
     * @throws Exception
     */
    public function __get($key)
    {
        return isset($this->object[$key]) ? $this->object[$key] : null;
    }
}
