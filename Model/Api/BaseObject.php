<?php

namespace Omise\Payment\Model\Api;

use Exception;

class BaseObject
{
    /**
     * @var mixed
     */
    protected $object;

    /**
     * @param  mixed $object  of \Omise\Payment\Model\Api\BaseObject.
     *
     * @return self
     */
    protected function refresh($object = null)
    {
        if ($this->object == null && $object == null) {
            return $this;
        }

        if ($object != null) {
            $this->object = $object;
        } elseif (method_exists($this->object, 'refresh')) {
            $this->object->refresh();
        }

        return $this;
    }

    /**
     * @param  string $key
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function __get($key)
    {
        return isset($this->object[$key]) ? $this->object[$key] : null;
    }
}
