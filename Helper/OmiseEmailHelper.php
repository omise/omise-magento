<?php
namespace Omise\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Omise\Payment\Model\Config\Cc as Config;

class OmiseEmailHelper extends AbstractHelper
{
    const STATE_PROCESSING = \Magento\Sales\Model\Order::STATE_PROCESSING;

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
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Config $config
     * @param \Magento\Framework\App\CacheInterface $cache
     */
    public function __construct(
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Helper\Context $context,
        Config $config,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->cache = $cache;

        parent::__construct($context);
    }

    public function sendInvoiceAndConfirmationEmails($order)
    {
        if (!$order->getEmailSent()) {
            $this->checkoutSession->setForceOrderMailSentOnSuccess(true);
            $this->orderSender->send($order, true);

            if ($this->config->getSendInvoiceAtOrderStatus() == self::STATE_PROCESSING) {
                $this->sendInvoiceEmail($order);
            }
        }
    }

    public function sendInvoiceEmail($order)
    {
        $this->checkoutSession->setForceInvoiceMailSentOnSuccess(true);

        $invoiceCollection = $order->getInvoiceCollection();
        foreach ($invoiceCollection as $invoice) {
            $id = $invoice->getId();
            if(!$this->cache->load('omise:invoice:sent:'. $id)) {
                $this->invoiceSender->send($invoice, true);
                $this->cache->save('1', 'omise:invoice:sent:'. $id, [], 300);
            }
        }
    }
}
