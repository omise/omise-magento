<?php

namespace Omise\Payment\Model\Api;

use Exception;
use OmiseCustomer;
use Omise\Payment\Model\Config\Config;

class Customer extends BaseObject
{
    /**
     * Injecting dependencies
     * @param \Omise\Payment\Model\Config\Config $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param  string $id
     *
     * @return Omise\Payment\Model\Api\Error|self
     */
    public function find($id)
    {
        if (!$this->config->canInitialize()) {
            return;
        }
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
