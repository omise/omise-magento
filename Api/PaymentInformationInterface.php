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

    /**
     * @param  int $order_id
     *
     * @return Object
     */
    public function paymentInfo($order_id);

}
