<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Model\Config\Cc as Config;

class PaymentThreeDSecureBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    const RETURN_URI = 'return_uri';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Omise\Payment\Model\Config\Cc
     */
    protected $config;

    public function __construct(UrlInterface $url, Config $config)
    {
        $this->url    = $url;
        $this->config = $config;
    }

    /**
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        if ($this->config->is3DSecureEnabled()) {
            return [
                self::RETURN_URI => $this->url->getUrl('omise/callback/threedsecure', ['_secure' => true])
            ];
        }

        return [];
    }
}
