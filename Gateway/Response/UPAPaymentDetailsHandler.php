<?php
namespace Omise\Payment\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment\Transaction;

class UPAPaymentDetailsHandler implements HandlerInterface
{
    /**
     * @var \Omise\Payment\Helper\OmiseHelper
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curlClient;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @param \Omise\Payment\Helper\OmiseHelper $helper
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param Transaction\BuilderInterface $transactionBuilder
     */
    public function __construct(
        \Omise\Payment\Helper\OmiseHelper $helper,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
    ) {
        $this->_helper            = $helper;
        $this->curlClient         = $curl;
        $this->transactionBuilder = $transactionBuilder;
    }

    /**
     * @param string $url URL to Tesco Barcode generated in Omise Backend
     * @return string Barcode in SVG format
     */
    private function downloadPaymentFile($url)
    {
        $this->curlClient->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curlClient->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->curlClient->get($url);
        return $this->curlClient->getBody();
    }
    
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment       = SubjectReader::readPayment($handlingSubject);
        $payment       = $payment->getPayment();
        
        $paymentType   = ($response['session']->object == "checkout_session") ? $response['session']->object : null;
        $paymentMethod = $payment->getMethod();
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
