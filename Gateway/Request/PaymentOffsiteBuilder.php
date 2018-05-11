<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Model\Config\Offsite\Alipay;
use Omise\Payment\Model\Config\Offsite\Internetbanking;
use Omise\Payment\Observer\OffsiteInternetbankingDataAssignObserver;

class PaymentOffsiteBuilder implements BuilderInterface
{

    /**
     *
     * @var string
     */
    const OFFSITE = 'offsite';

    /**
     *
     * @var string
     */
    const RETURN_URI = 'return_uri';

    /**
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    public function __construct(UrlInterface $url)
    {
        $this->url = $url;
    }

    /**
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentInfo = [
            self::RETURN_URI => $this->url->getUrl('omise/callback/offsite', [
                '_secure' => true
            ])
        ];

        $payment = SubjectReader::readPayment($buildSubject);
        $method = $payment->getPayment();
        
        switch ($method->getMethod()) {
            case Alipay::CODE:
                $paymentInfo[self::OFFSITE] = 'alipay';
                break;
            case Internetbanking::CODE:
                $paymentInfo[self::OFFSITE] = $method->getAdditionalInformation(OffsiteInternetbankingDataAssignObserver::OFFSITE);
                break;
            default:
                break;
        }

        return $paymentInfo;
    }
}
