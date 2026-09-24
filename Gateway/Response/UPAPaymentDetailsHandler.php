<?php
namespace Omise\Payment\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Omise\Payment\Helper\OmiseHelper;

class UPAPaymentDetailsHandler implements HandlerInterface
{
    /**
     * @var OmiseHelper
     */
    protected $helper;

    /**
     * @param OmiseHelper $helper
     */
    public function __construct(
        OmiseHelper $helper
    ) {
        $this->helper = $helper;
    }
    
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment       = SubjectReader::readPayment($handlingSubject);
        $payment       = $payment->getPayment();
        
        $methodId = $this->helper->getMethodId($payment->getMethod());
        $paymentType = $response['session']->object === 'checkout_session'
            ? ($methodId ?? $response['session']->object)
            : null;
        $order         = $payment->getOrder();

        $payment->setAdditionalInformation('upa_redirect_uri', $response['session']->redirect_url);
        $payment->setAdditionalInformation('session_id', $response['session']->id);
        $payment->setAdditionalInformation('payment_type', $paymentType);

        $order->addStatusHistoryComment(
            $payment->prependMessage(
                __(
                    'Processing amount of %1 via Omise Checkout Gateway. checkout session ID: %2',
                    $order->getBaseCurrency()->formatTxt($order->getTotalDue()),
                    $response['session']->id
                )
            )
        );
    }
}
