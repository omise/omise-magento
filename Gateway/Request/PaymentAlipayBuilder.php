<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class PaymentAlipayBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    const OFFSITE = 'offsite';

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
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $method  = $payment->getPayment();

        return [
            self::OFFSITE    => 'alipay',
            self::RETURN_URI => $this->url->getUrl('omise/callback/offsite', ['_secure' => true])
        ];
    }
}
