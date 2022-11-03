<?php

namespace Omise\Payment\Observer;

use Exception;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Helper\OmiseHelper;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use OmiseCharge;

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
    public function __construct(Config $config, OmiseHelper $helper)
    {
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
            $order = $observer->getEvent();
            if(!$order) {
                return $this;
            }
            $payment = $order->getPayment();
            if(!$payment) {
                return $this;
            }
            $method = $payment->getMethod();
            // if method is not omise return and end
            if (strpos($method, 'omise')) {
               return $this;
            }
            $chargeId = $payment->getAdditionalInformation('charge_id');
            if(!$chargeId) {
                return $this;
            }
            $charge = OmiseCharge::retrieve($chargeId, $this->config->getPublicKey(), $this->config->getSecretKey());
            $amountToRefund = $this->helper->omiseAmountFormat($charge['currency'], $payment['base_amount_refunded']);
            $charge->refund(['amount' => $amountToRefund]);
            return $this;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
