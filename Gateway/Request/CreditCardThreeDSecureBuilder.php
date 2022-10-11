<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Helper\ReturnUrlHelper;

class CreditCardThreeDSecureBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    const RETURN_URI = 'return_uri';

    /**
     * @var \Omise\Payment\Helper\ReturnUrlHelper
     */
    protected $returnUrl;

    /**
     * Injecting dependencies
     *
     * @param $returnUrl ReturnUrlHelper
     */
    public function __construct(ReturnUrlHelper $returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $returnUrl = $this->returnUrl->create('omise/callback/threedsecure');

        $payment = $buildSubject['payment']->getPayment();
        $payment->setAdditionalInformation('token', $returnUrl['token']);

        return [ self::RETURN_URI => $returnUrl['url'] ];
    }
}
