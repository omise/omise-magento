<?php
namespace Omise\Payment\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment\Transaction;

class PaymentDetailsHandler implements HandlerInterface
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
        $paymentType   = isset($response['charge']->source['type']) ? $response['charge']->source['type'] : null;
        $paymentMethod = $payment->getMethod();
        $order         = $payment->getOrder();

        $payment->setAdditionalInformation('charge_id', $response['charge']->id);
        $payment->setAdditionalInformation('charge_authorize_uri', $response['charge']->authorize_uri);
        $payment->setAdditionalInformation('payment_type', $paymentType);

        $transaction = $this->transactionBuilder
                            ->setPayment($payment)
                            ->setOrder($order)
                            ->setTransactionId($response['charge']->id)
                            ->setAdditionalInformation([Transaction::RAW_DETAILS => (array) $payment])
                            ->setFailSafe(true)
                            ->build(Transaction::TYPE_PAYMENT);
        $payment->addTransactionCommentsToOrder(
            $transaction,
            $payment->prependMessage(
                __(
                    'Processing amount of %1 via Omise Payment Gateway.',
                    $order->getBaseCurrency()->formatTxt($order->getTotalDue())
                )
            )
        );
        
        if ($paymentType === 'bill_payment_tesco_lotus') {
            $barcode = $this->downloadPaymentFile($response['charge']->source['references']['barcode']);
            $payment->setAdditionalInformation('barcode', $barcode);
        }

        if ($this->_helper->isPayableByImageCode($paymentMethod)) {
            $payment->setAdditionalInformation(
                'image_code',
                $response['charge']->source['scannable_code']['image']['download_uri']
            );
        }
    }
}
