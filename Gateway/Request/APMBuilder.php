<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

use Omise\Payment\Model\Config\Alipay;
use Omise\Payment\Model\Config\Conveniencestore;
use Omise\Payment\Model\Config\Pointsciti;
use Omise\Payment\Model\Config\Internetbanking;
use Omise\Payment\Model\Config\Installment;
use Omise\Payment\Model\Config\Tesco;
use Omise\Payment\Model\Config\Paynow;
use Omise\Payment\Model\Config\Truemoney;

use Omise\Payment\Observer\ConveniencestoreDataAssignObserver;
use Omise\Payment\Observer\InstallmentDataAssignObserver;
use Omise\Payment\Observer\InternetbankingDataAssignObserver;
use Omise\Payment\Observer\TruemoneyDataAssignObserver;

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
    const SOURCE_PHONE_NUMBER = 'phone_number';

    /**
     * @var string
     */
    const SOURCE_NAME = 'name';

    /**
     * @var string
     */
    const SOURCE_EMAIL = 'email';

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
            case Truemoney::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE         => 'truemoney',
                    self::SOURCE_PHONE_NUMBER => $method->getAdditionalInformation(TruemoneyDataAssignObserver::PHONE_NUMBER),
                ];
                break;
            case Conveniencestore::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE         => 'econtext',
                    self::SOURCE_PHONE_NUMBER => $method->getAdditionalInformation(ConveniencestoreDataAssignObserver::PHONE_NUMBER),
                    self::SOURCE_EMAIL        => $method->getAdditionalInformation(ConveniencestoreDataAssignObserver::EMAIL),
                    self::SOURCE_NAME         => $method->getAdditionalInformation(ConveniencestoreDataAssignObserver::CUSTOMER_NAME)
                ];
                break;
<<<<<<< HEAD
            case Paynow::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'paynow'
=======
            case Pointsciti::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'points_citi'
>>>>>>> master
                ];
                break;
        }

        return $paymentInfo;
    }
}
