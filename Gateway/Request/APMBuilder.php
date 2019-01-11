<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Model\Config\Alipay;
use Omise\Payment\Model\Config\Installment;
use Omise\Payment\Model\Config\Internetbanking;
use Omise\Payment\Model\Config\Tesco;
use Omise\Payment\Observer\InstallmentDataAssignObserver;
use Omise\Payment\Observer\InternetbankingDataAssignObserver;

class APMBuilder implements BuilderInterface
{

    /**
     * @var string
     */
    const SOURCE = 'source';

    /**
     * @var string
     */
    const SOURCE_TYPE = 'type';

    /**
     * @var string
     */
    const SOURCE_INSTALLMENT_TERMS = 'installment_terms';

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
        $paymentInfo = [
            self::RETURN_URI => $this->url->getUrl('omise/callback/offsite', [
                '_secure' => true
            ])
        ];

        $payment = SubjectReader::readPayment($buildSubject);
        $method  = $payment->getPayment();

        switch ($method->getMethod()) {
            case Alipay::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'alipay'
                ];
                break;
            case Tesco::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'bill_payment_tesco_lotus'
                ];
                break;
            case Internetbanking::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => $method->getAdditionalInformation(InternetbankingDataAssignObserver::OFFSITE)
                ];
                break;
            case Installment::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE              => $method->getAdditionalInformation(InstallmentDataAssignObserver::OFFSITE),
                    self::SOURCE_INSTALLMENT_TERMS => $method->getAdditionalInformation(InstallmentDataAssignObserver::TERMS),
                ];
                break;
        }

        return $paymentInfo;
    }
}
