<?php
namespace Omise\Payment\Gateway\Http\Client;

class OffsiteInternetbanking extends AbstractOmiseClient
{
    /**
     * @param  array $body
     *
     * @return array
     */
    public function request(Array $body)
    {
        return \OmiseCharge::create($body, $this->publicKey, $this->secretKey);
    }
}
