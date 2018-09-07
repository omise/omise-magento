<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class CreditCardThreeDSecureBuilder implements BuilderInterface
{
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
        return [
            self::RETURN_URI => $this->url->getUrl('omise/callback/threedsecure', ['_secure' => true])
        ];
    }
}
