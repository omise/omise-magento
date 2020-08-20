<?php
namespace Omise\Payment\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * $var \Omise\Payment\Helper\OmiseHelper
     */
     protected $_helper;

     /**
     * @param \Omise\Payment\Helper\OmiseHelper $helper
     */
    public function __construct(
        \Omise\Payment\Helper\OmiseHelper $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * @param string $url URL to Tesco Barcode generated in Omise Backend
     * @return string Barcode in SVG format
     */
    private function downloadPaymentFile($url) 
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        return curl_exec($ch);
    }
    
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = SubjectReader::readPayment($handlingSubject);
        $payment = $payment->getPayment();

        $payment->setAdditionalInformation('charge_id', $response['charge']->id);
        $payment->setAdditionalInformation('charge_authorize_uri', $response['charge']->authorize_uri);
        $payment->setAdditionalInformation('payment_type', $response['charge']->source['type']);
        
        if ($response['charge']->source['type'] === 'bill_payment_tesco_lotus') {
            $barcode = $this->downloadPaymentFile($response['charge']->source['references']['barcode']);
            $payment->setAdditionalInformation('barcode', $barcode);
        }

        if ($this->_helper->isPayableByImageCode($response['charge']->source['type'])) {
            $payment->setAdditionalInformation('image_code', $response['charge']->source['scannable_code']['image']['download_uri']);
        }
    }
}
