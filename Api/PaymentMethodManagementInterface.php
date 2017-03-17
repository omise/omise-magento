<?php
namespace Omise\Payment\Api;

interface PaymentMethodManagementInterface
{
    /**
     * @param  int $orderId
     *
     * @return string
     */
    public function get3DSecureAuthorizeUri($orderId);
}
