<?php

namespace Omise\Payment\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class Cancel extends Action
{
    protected $checkoutSession;
    protected $orderRepository;
    protected $logger;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $lastOrderId = $this->checkoutSession->getLastOrderId();
            if ($lastOrderId) {
                $order = $this->orderRepository->get($lastOrderId);
                $allowedStates = [
                    \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
                    \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW
                ];
                if (in_array($order->getState(), $allowedStates)) {
                    $order->registerCancellation('Payment cancelled by customer (Omise cancel).');
                    $this->orderRepository->save($order);
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Order Cancel Error: ' . $e->getMessage());
        } finally {
            $this->checkoutSession->restoreQuote();
        }
        return $this->_redirect('checkout/cart', ['_secure' => true]);
    }
}
