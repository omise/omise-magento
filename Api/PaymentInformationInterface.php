<?php
namespace Omise\Payment\Api;

interface PaymentInformationInterface
{
    /**
     * @param  int $order_id
     *
     * @return Omise\Payment\Api\Data\PaymentInterface
     */
    public function offsite($order_id);
}
