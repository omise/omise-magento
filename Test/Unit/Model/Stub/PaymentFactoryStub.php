<?php

namespace Omise\Payment\Test\Unit\Model\Stub;

use Omise\Payment\Api\Data\PaymentInterface;

class PaymentFactoryStub
{
    private PaymentInterface $data;

    public function __construct(PaymentInterface $data)
    {
        $this->data = $data;
    }

    public function create(): PaymentInterface
    {
        return $this->data;
    }
}
