<?php
namespace Omise\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class OmiseEmailHelper extends AbstractHelper
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
        protected $orderModel;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderModel
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Helper\Context $context
     *
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderModel,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->orderModel = $orderModel;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function sendInvoiceAndConfirmationEmails($order)
    {
        $invoiceCollection = $order->getInvoiceCollection();

        $this->checkoutSession->setForceOrderMailSentOnSuccess(true);
        $this->orderSender->send($order, true);

        foreach ($invoiceCollection as $invoice) {
            $this->invoiceSender->send($invoice, true);
        }
    }
}
