<?php
namespace Omise\Payment\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\OmiseEmailHelper;
use Omise\Payment\Model\Config\Cc as Config;

class UPAPendingInvoiceHandler implements HandlerInterface
{
    const ACTION_AUTHORIZE_CAPTURE             = \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
    const STATE_PROCESSING = \Magento\Sales\Model\Order::STATE_PROCESSING;

    /**
     * @var OmiseEmailHelper
     */
    private $emailHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param OmiseEmailHelper $emailHelper
     * @param Config $config
     */
    public function __construct(
        OmiseEmailHelper $emailHelper,
        Config $config
    ) {
        $this->emailHelper = $emailHelper;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        if ($this->config->getSendInvoiceAtOrderStatus() == self::STATE_PROCESSING) {
            return;
        }
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface **/
        $payment = SubjectReader::readPayment($handlingSubject);

        $invoice = $payment->getPayment()->getOrder()->prepareInvoice();
        $invoice->register();
        $payment->getPayment()->getOrder()->addRelatedObject($invoice)->save();

        $this->emailHelper->sendInvoiceEmail($payment->getPayment()->getOrder());
    }
}
