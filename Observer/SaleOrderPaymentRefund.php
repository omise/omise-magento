<?php

namespace Omise\Payment\Observer;

use OmiseCharge;
use Exception;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Helper\OmiseHelper;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;

class SaleOrderPaymentRefund implements ObserverInterface
{
    /**
     * Omise\Payment\Model\Config\Config
     */
    private $config;

    /**
     * @param Config $config
     * @param OmiseHelper $helper
     */
    public function __construct(
        Config $config,
        OmiseHelper $helper
    ) {
        $this->config = $config;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            $payment = $event->getPayment();
            $order = $payment->getOrder();
            if (!$payment) {
                return new LocalizedException(__('Payment not found'));
            }
            $method = $payment->getMethod();
            // if method contain omise
            if (strpos($method, 'omise') !== false) {
                $this->config->setStoreId($order['store_id']);
                $chargeId = $payment->getAdditionalInformation('charge_id');
                if (!$chargeId) {
                    return $this;
                }
                $charge = OmiseCharge::retrieve(
                    $chargeId,
                    $this->config->getPublicKey(),
                    $this->config->getSecretKey()
                );
                if (!$charge['refundable']) {
                    $methodName = str_replace('_', ' ', $method);
                    throw new LocalizedException(__('Payment by %1 cannot be refunded.', $methodName));
                }
                $amountToRefund = $this->helper->omiseAmountFormat(
                    $charge['currency'],
                    $payment['base_amount_refunded']
                );
                $charge->refund(['amount' => $amountToRefund]);
                $order->addStatusHistoryComment(
                    __(
                        'Omise: Payment refunded.<br/>An amount %1 %2 has been refunded.',
                        number_format($payment['base_amount_refunded'], 2, '.', ''),
                        $charge['currency']
                    )
                );
                $order->save();
            }
            return $this;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
