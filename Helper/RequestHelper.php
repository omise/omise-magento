<?php

namespace Omise\Payment\Helper;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\Header;
use Omise\Payment\Model\Omise;
use OmiseException;

class RequestHelper
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var Omise
     */
    private $omise;

    /**
     * @var int
     */
    private $omiseConnectTimeout = 30;

    /**
     * @var int
     */
    private $omiseTimeout = 60;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $header;

    /**
     * @param RequestInterface $request
     * @param Header $header
     * @param Omise $omise
     */
    public function __construct(
        RequestInterface $request,
        Header $header,
        Omise $omise
    ) {
        $this->request = $request;
        $this->omise = $omise;
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

    /**
     * Request helper for UPA session API
     * @param string $url
     * @param string $requestMethod
     * @param string $skey
     * @param array $params
     * @param bool $is_json
     * @return array
     */
    public function sendUpaSessionRequest($url, $requestMethod, $skey, $params = [], $is_json = false)
    {
        return $this->upaRequest(
            $url,
            $requestMethod,
            $skey,
            $params,
            $is_json
        );
    }

    /**
     * @param  string $url
     * @param  string $requestMethod
     * @param  string $key
     * @param  array  $params
     * @param  bool   $is_json
     * @return array
     * @codeCoverageIgnore
     */
    private function upaRequest($url, $requestMethod, $key, $params = null, $is_json = false)
    {
        try {
            $response = $this->execute($url, $requestMethod, $key, $params, $is_json);
            $array = json_decode($response, true);
            // If response is invalid or not a JSON.
            if (!$this->isValidAPIResponse($array)) {
                throw new \Exception('Unknown error. (Bad Response)');
            }

            if (!empty($array['object']) && $array['object'] === 'error') {
                throw \OmiseException::getInstance($array);
            }
            return $array;
        } catch (OmiseException $e) {
            throw new OmiseException($e->getMessage());
        }
    }
    /**
     * @param  string $url
     * @param  string $requestMethod
     * @param  string $key
     * @param  array  $params
     * @param  bool   $is_json
     * @return string
     * @codeCoverageIgnore
     */
    private function execute($url, $requestMethod, $key, $params = null, $is_json = false)
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, $this->genOptions($requestMethod, $key . ':', $params, $is_json));

        // Make a request or thrown an exception.
        if (($result = curl_exec($ch)) === false) {
            $error = curl_error($ch);
            curl_close($ch);

            throw new \Exception($error);
        }

        // Close.
        curl_close($ch);

        return $result;
    }

    /**
     * Creates an option for php-curl from the given request method and parameters in an associative array.
     *
     * @param  string $requestMethod
     * @param  array  $params
     *
     * @return array
     * @codeCoverageIgnore
     */
    private function genOptions($requestMethod, $userpwd, $params, $is_json)
    {
        $options = [
            // Set the HTTP version to 1.1.
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            // Set the request method.
            CURLOPT_CUSTOMREQUEST => $requestMethod,
            // Make php-curl returns the data as string.
            CURLOPT_RETURNTRANSFER => true,
            // Do not include the header in the output.
            CURLOPT_HEADER => false,
            // Track the header request string and set the referer on redirect.
            CURLINFO_HEADER_OUT => true,
            CURLOPT_AUTOREFERER => true,
            // Make HTTP error code above 400 an error.
            // CURLOPT_FAILONERROR => true,
            // Time before the request is aborted.
            CURLOPT_TIMEOUT => $this->omiseTimeout,
            // Time before the request is aborted when attempting to connect.
            CURLOPT_CONNECTTIMEOUT => $this->omiseConnectTimeout,
            // Authentication.
            CURLOPT_USERPWD => $userpwd
        ];

        // Config UserAgent
        if (defined('OMISE_USER_AGENT_SUFFIX')) {
            $options += [CURLOPT_USERAGENT => OMISE_USER_AGENT_SUFFIX];
        } else {
            $this->omise->defineUserAgent();
            $options += [CURLOPT_USERAGENT => OMISE_USER_AGENT_SUFFIX];
        }

        if ($is_json) {
            $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
            $http_query = json_encode($params);
            $options += [CURLOPT_POSTFIELDS => $http_query];
            return $options;
        }

        // Also merge POST parameters with the option.
        if (is_array($params) && count($params) > 0) {
            $http_query = http_build_query($params);
            $http_query = preg_replace('/%5B\d+%5D/simU', '%5B%5D', $http_query);

            $options += [CURLOPT_POSTFIELDS => $http_query];
        }
        return $options;
    }

    /**
     * Checks if response from API was valid.
     *
     * @param  array  $array  - decoded JSON response
     *
     * @return boolean
     * @codeCoverageIgnore
     */
    private static function isValidAPIResponse($array)
    {
        return $array && count($array) && isset($array['object']);
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
}
