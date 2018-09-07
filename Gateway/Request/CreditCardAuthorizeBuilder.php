<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class CreditCardAuthorizeBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    const CAPTURE = 'capture';

    /**
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        return [ self::CAPTURE => false ];
    }
}
