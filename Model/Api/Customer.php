<?php

namespace Omise\Payment\Model\Api;

use Exception;
use OmiseCustomer;

class Customer extends Object
{
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
}
