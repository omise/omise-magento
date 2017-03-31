<?php
namespace Omise\Payment\Model\Data;

use Omise\Payment\Api\Data\PaymentInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

class Payment extends AbstractExtensibleObject implements PaymentInterface
{
    /**
     * @api
     *
     * @param  int $value
     *
     * @return self
     */
    public function setOrderId($value)
    {
        return $this->setData(self::ORDER_ID, $value);
    }

    /**
     * @api
     *
     * @param  string $value
     *
     * @return self
     */
    public function setAuthorizeUri($value)
    {
        return $this->setData(self::AUTHORIZE_URI, $value);
    }

    /**
     * Always return a "payment" string.
     *
     * @api
     *
     * @return string
     */
    public function getObject()
    {
        return 'payment';
    }

    /**
     * @api
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * @api
     *
     * @return string|null
     */
    public function getAuthorizeUri()
    {
        return $this->_get(self::AUTHORIZE_URI);
    }
}
