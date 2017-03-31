<?php
namespace Omise\Payment\Api\Data;

interface PaymentInterface
{
    // Fields
    const TYPE          = 'object';
    const ORDER_ID      = 'order_id';
    const AUTHORIZE_URI = 'authorize_uri';

    /**
     * @api
     *
     * @param  int $value
     *
     * @return self
     */
    public function setOrderId($value);

    /**
     * @api
     *
     * @param  string $value
     *
     * @return self
     */
    public function setAuthorizeUri($value);

    /**
     * Always return a "payment" string.
     *
     * @api
     *
     * @return string
     */
    public function getObject();

    /**
     * @api
     *
     * @return int
     */
    public function getOrderId();

    /**
     * @api
     *
     * @return string|null
     */
    public function getAuthorizeUri();
}
