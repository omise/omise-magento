<?php
namespace Omise\Payment\Controller\Callback;

use Magento\Sales\Model\Order;
use Omise\Payment\Model\Config\Alipay;
use Omise\Payment\Model\Api\Charge as APICharge;

class OffsiteAlipay extends Base
{
    /**
     * {@inheritdoc}
     */
    public function validate(Order $order, Invoice $invoice, Payment $payment, APICharge $charge)
    {
        if ($order->getState() !== Order::STATE_PENDING_PAYMENT) {
            throw new Exception(__('Invalid order status, cannot validate the payment. Please contact our support if you have any questions.'));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPaymentMethodCode()
    {
        return Alipay::CODE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPaymentMethodTitle()
    {
        return Alipay::TITLE;
    }
}
