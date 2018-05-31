<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Observer\OmiseDataAssignObserver;


class PaymentOffsiteBuilder implements BuilderInterface
{

    /**
     * @var string
     */
    const OFFSITE = 'offsite';
    const SOURCE = 'source';
    /**
     * @var string
     */
    const RETURN_URI = 'return_uri';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    public function __construct(UrlInterface $url)
    {
        $this->url = $url;
    }

    /**
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $method  = $payment->getPayment();
        
        $paymentInfo = [
            self::RETURN_URI => $this->url->getUrl('omise/callback/offsite', [
                '_secure' => true
            ]),
            self::SOURCE => $method->getAdditionalInformation(OmiseDataAssignObserver::SOURCE),
        ];
        
        return $paymentInfo;
    }
}
