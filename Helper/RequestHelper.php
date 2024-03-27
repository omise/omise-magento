<?php

namespace Omise\Payment\Helper;

class RequestHelper
{
    public function get_client_ip()
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

        foreach($headersToCheck as $header) {
            if (empty($_SERVER[$header])) {
                continue;
            }

            if ($header === 'HTTP_X_FORWARDED_FOR') {
                return self::process_forwarded_for_header($_SERVER[$header]);
            }

            return $_SERVER[$header];
        }

        // return default remote IP address
        return $_SERVER['REMOTE_ADDR'];
    }

    private function process_forwarded_for_header($forwardedForHeader)
    {
        // Split if multiple IP addresses exist and get the last IP address
        if (strpos($forwardedForHeader, ',') !== false) {
            $multiple_ips = explode(",", $forwardedForHeader);
            return trim(current($multiple_ips));
        }
        return $forwardedForHeader;
    }
}