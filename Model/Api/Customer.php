<?php

namespace Omise\Payment\Model\Api;

use Exception;
use OmiseCustomer;

class Customer extends BaseObject
{
    /**
     * @param  string $id
     *
     * @return Omise\Payment\Model\Api\Error|self
     */
    public function find($id)
    {
        try {
            $this->refresh(OmiseCustomer::retrieve($id));
        } catch (Exception $e) {
            return new Error([
                'code'    => 'not_found',
                'message' => $e->getMessage()
            ]);
        }

        return $this;
    }

    /**
     * @param  array $params
     *
     * @return Omise\Payment\Model\Api\Error|self
     */
    public function create($params)
    {
        try {
            $this->refresh(OmiseCustomer::create($params));
        } catch (Exception $e) {
            return new Error([
                'code'    => 'bad_request',
                'message' => $e->getMessage()
            ]);
        }

        return $this;
    }

     /**
     * @param  array $params
     *
     * @return \Omise\Payment\Model|\Api\Error|self
     */
    public function update($params)
    {
        try {
            $this->object->update($params);
            $this->refresh($this->object);
        } catch (Exception $e) {
            return new Error([
                'code'    => 'bad_request',
                'message' => $e->getMessage()
            ]);
        }

        return $this;
    }

    /**
     * TODO: Need to refactor a bit
     */
    public function cards($options = [])
    {
        return $this->object->cards($options);
    }
}
