<?php
namespace Omise\Payment\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

class PaymentCommentsHandler implements HandlerInterface
{

    public function handle(array $handlingSubject, array $response)
    {
        return $this;
    }
}
