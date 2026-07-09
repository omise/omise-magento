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
     * @param \Omise\Payment\Helper\OmiseHelper $helper
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     */
    public function __construct(
        \Omise\Payment\Helper\OmiseHelper $helper,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->_helper    = $helper;
        $this->curlClient = $curl;
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

        $payment->setTransactionId($response['charge']->id);
        $payment->setAdditionalInformation('charge_id', $response['charge']->id);
        $payment->setAdditionalInformation('charge_authorize_uri', $response['charge']->authorize_uri);
        $payment->setAdditionalInformation('payment_type', $paymentType);
        $payment->setAdditionalInformation('charge_expires_at', $response['charge']->expires_at);

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

        // only save useful payment additional_information into transaction additional_information
        $payment->setTransactionAdditionalInfo(Transaction::RAW_DETAILS, (array) $payment->getAdditionalInformation());

        // use back payment module function to generate transaction
        $transaction = $payment->addTransaction(Transaction::TYPE_PAYMENT, null, true);

        $payment->addTransactionCommentsToOrder(
            $transaction,
            $payment->prependMessage(
                __(
                    'Processing amount of %1 via Opn Payments Gateway.',
                    $order->getBaseCurrency()->formatTxt($order->getTotalDue())
                )
            )
        );
    }
}
