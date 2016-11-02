<?php
namespace Omise\Payment\Gateway\Http;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class OmiseClient implements ClientInterface
{
    
    public function __construct() {
    }
    
    public function placeRequest(TransferInterface $transferObject)
    {
        $response = [];
        return $response;
    }
}
