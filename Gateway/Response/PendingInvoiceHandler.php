<?php
namespace Omise\Payment\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Omise\Payment\Helper\OmiseHelper;

class PendingInvoiceHandler implements HandlerInterface
{
    const ACTION_AUTHORIZE_CAPTURE             = \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE;

    /**
     * @var OmiseHelper
     */
    private $helper;

    /**
     * @param OmiseHelper $helper
     */
    public function __construct(OmiseHelper $helper)
    {
        $this->helper = $helper;
    }
    
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $is3dsecured = $this->helper->is3DSecureEnabled($response['charge']);
        if(!$is3dsecured && $handlingSubject['paymentAction'] != self::ACTION_AUTHORIZE_CAPTURE) {
            return;
        }
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface **/
        $payment = SubjectReader::readPayment($handlingSubject);

        $invoice = $payment->getPayment()->getOrder()->prepareInvoice();
        $invoice->register();

        $payment->getPayment()->getOrder()->addRelatedObject($invoice);
    }
}
