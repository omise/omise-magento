<?php
namespace Omise\Payment\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment\Transaction;

class UPAPaymentDetailsHandler implements HandlerInterface
{
    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @param Transaction\BuilderInterface $transactionBuilder
     */
    public function __construct(
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
    ) {
        $this->transactionBuilder = $transactionBuilder;
    }
    
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment       = SubjectReader::readPayment($handlingSubject);
        $payment       = $payment->getPayment();
        
        $paymentType   = ($response['session']->object == "checkout_session") ? $response['session']->object : null;
        $order         = $payment->getOrder();

        $payment->setAdditionalInformation('upa_redirect_uri', $response['session']->redirect_url);
        $payment->setAdditionalInformation('session_id', $response['session']->id);
        $payment->setAdditionalInformation('payment_type', $paymentType);

        $transaction = $this->transactionBuilder
                            ->setPayment($payment)
                            ->setOrder($order)
                            ->setTransactionId($response['session']->id)
                            ->setAdditionalInformation([Transaction::RAW_DETAILS => (array) $payment])
                            ->setFailSafe(true)
                            ->build(Transaction::TYPE_PAYMENT);
        $payment->addTransactionCommentsToOrder(
            $transaction,
            $payment->prependMessage(
                __(
                    'Processing amount of %1 via Omise Checkout Gateway.',
                    $order->getBaseCurrency()->formatTxt($order->getTotalDue())
                )
            )
        );
    }
}
