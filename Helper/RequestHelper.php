<?php

namespace Omise\Payment\Helper;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\Header;

class RequestHelper
{
    /**
     * @var Magento\Framework\App\RequestInterface;

     */
    private $request;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $header;

    public function __construct(
        RequestInterface $request,
        Header $header
    ) {
        $this->request = $request;
        $this->header = $header;
    }

    public function getClientIp()
    {
        $headersToCheck = [
            // Check for a client using a shared internet connection
            'HTTP_CLIENT_IP',

            // Check if the proxy is used for IP/IPs
            'HTTP_X_FORWARDED_FOR',

            // check for other possible forwarded IP headers
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
        ];

        foreach ($headersToCheck as $header) {
            $headerValue = $this->request->getServer($header, '');

            if (empty($headerValue)) {
                continue;
            }

            if ($header === 'HTTP_X_FORWARDED_FOR' && !empty($headerValue)) {
                return $this->processForwardedForHeader($headerValue);
            }

            return $headerValue;
        }

        // return default remote IP address
        return $this->request->getServer('REMOTE_ADDR', '');
    }

    private function processForwardedForHeader($forwardedForHeader)
    {
        // Split if multiple IP addresses exist and get the last IP address
        if (strpos($forwardedForHeader, ',') !== false) {
            $multiple_ips = explode(",", $forwardedForHeader);
            return trim(current($multiple_ips));
        }

        return $forwardedForHeader;
    }

    /**
     * Get platform Type of WEB, IOS or ANDROID to add to source API parameter.
     * @return string
     */
    public function getPlatformType()
    {
        $userAgent = $this->header->getHttpUserAgent();

        if (preg_match("/(Android)/i", $userAgent)) {
            return "ANDROID";
        }

        if (preg_match("/(iPad|iPhone|iPod)/i", $userAgent)) {
            return "IOS";
        }

        return "WEB";
    }

    /**
     * Check if current platform is mobile or not
     */
    public function isMobilePlatform()
    {
        return 'WEB' !== $this->getPlatformType();
    }
}
