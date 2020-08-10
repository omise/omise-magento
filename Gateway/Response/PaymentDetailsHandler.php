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
     * @var Omise\Payment\Model\Config\Config
     */
     protected $_config;

     /**
      * @var \Magento\Framework\Filesystem
      */
     protected $_filesystem;

     /**
     * @param \Omise\Payment\Helper\OmiseHelper $helper
     * @param \Omise\Payment\Model\Config\Config $config
     * @param \Magento\Framework\Filesystem $filesystem $filesystem
     */
    public function __construct(
        \Omise\Payment\Helper\OmiseHelper $helper,
        \Omise\Payment\Model\Config\Config $config,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_helper = $helper;
        $this->_config = $config;
        $this->_filesystem = $filesystem;
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
     * saves QR code in the media directory of magento.
     * returns file path.
     * @param \Magento\Payment\Gateway\Data\PaymentDataObject $paymentType
     * @param string $qrCodeImage
     * @return string
     */
    private function saveQr($paymentType, $qrCodeImage)
    {
        $filename = \Omise\Payment\Model\Config\Config::MEDIA_STORAGE_LOCATION;
        $filename .= uniqid($paymentType."_");
        if($this->_config->isSandboxEnabled()) {
            $filename .= ".png";
        } else {
            $filename .= ".svg";
        }
        $fs = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $fs->writeFile($filename, $qrCodeImage);
        return $filename;
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
            $barcode = $this->downloadPaymentFile($response['charge']->source['references']['barcode']);
            $payment->setAdditionalInformation('barcode', $barcode);
            return;
        }

        if ($this->_helper->isOfflinePayment($paymentType)) {
            $qrCodeImage = $this->downloadPaymentFile($response['charge']->source['scannable_code']['image']['download_uri']);
            $payment->setAdditionalInformation('qr_code_encoded', base64_encode($qrCodeImage));
            if($paymentType == 'promptpay') {
                $filename = $this->saveQr($paymentType, $qrCodeImage);
                $payment->setAdditionalInformation('qr_code', $filename);
            }
        }
    }
}
