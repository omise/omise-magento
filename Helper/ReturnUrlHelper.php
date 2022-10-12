<?php

namespace Omise\Payment\Helper;

use Omise\Payment\Helper\TokenHelper;
use Magento\Framework\UrlInterface;

class ReturnUrlHelper
{
    public function __construct(UrlInterface $url, TokenHelper $tokenHelper)
    {
        $this->url = $url;
        $this->tokenHelper = $tokenHelper;
    }

    public function create($returnUriString)
    {
        $queryParams = [ 'token' => $this->tokenHelper->random() ];
        $returnUri = $returnUriString . '?' . http_build_query($queryParams);

        return [
            'url' => $this->url->getUrl($returnUri, ['_secure' => true]),
            'token' => $queryParams['token']
        ];
    }
}
