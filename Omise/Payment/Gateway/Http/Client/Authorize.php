<?php

namespace Omise\Payment\Gateway\Http\Client;

class Authorize extends AbstractOmiseClient
{
    /**
     * @param  array $body
     *
     * @return array
     */
    public function request(Array $body)
    {
        return \OmiseCharge::create([
            'amount'   => 10000,
            'currency' => "thb",
            'card'     => $body['omise_card_token'],
            'capture'  => false
        ], $this->publicKey, $this->secretKey);
    }
}
