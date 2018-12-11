<?php

namespace Omise\Payment\Model\Api;

use Exception;
use OmiseEvent;

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
class Event extends BaseObject
{
    /**
     * @param  string $id
     *
     * @return \Omise\Payment\Model\Api\Error|self
     */
    public function find($id)
    {
        try {
            $event         = OmiseEvent::retrieve($id);
            $event['data'] = $this->transformDataToObject($event['data']);
            $this->refresh($event);
        } catch (Exception $e) {
            return new Error([
                'code'    => 'not_found',
                'message' => $e->getMessage()
            ]);
        }

        return $this;
    }

    /**
     * @param  json-object $data
     *
     * @return \Omise\Payment\Model\Api\Error|json-object
     */
    protected function transformDataToObject($data)
    {
        switch ($data['object']) {
            case 'charge':
                $data = (new Charge)->find($data['id']);
                break;
        }

        return $data;
    }
}