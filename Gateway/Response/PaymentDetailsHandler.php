<?php
namespace Omise\Payment\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * $var \Omise\Payment\Helper\OmiseHelper
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var string
     */
    protected $imageData;

    /**
     * @var string
     */
    protected $imageType;

     /**
     * @param \Omise\Payment\Helper\OmiseHelper $helper
     * @param \Magento\Framework\Filesystem $filesystem $filesystem
     */
    public function __construct(
        \Omise\Payment\Helper\OmiseHelper $helper,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_helper = $helper;
        $this->_filesystem = $filesystem;
    }

    /**
     * @desc Gets image data and data type from given $url
     * @param string $url URL to image generated in Omise Backend
     * @return void
     */
    private function setPaymentFileData($url) 
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $this->imageData = curl_exec($ch);
        $this->imageType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
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
        $paymentType = $response['charge']->source['type'];
        if ($paymentType === 'bill_payment_tesco_lotus') {
            $this->setPaymentFileData($response['charge']->source['references']['barcode']);
            $payment->setAdditionalInformation('barcode', $this->imageData);
            return;
        }

        if ($this->_helper->isOfflinePayment($paymentType)) {
            $this->setPaymentFileData($response['charge']->source['scannable_code']['image']['download_uri']);
            $payment->setAdditionalInformation('qr_code_encoded', base64_encode($this->imageData));
            $payment->setAdditionalInformation('qr_data_type', $this->imageType);
        }
    }
}
