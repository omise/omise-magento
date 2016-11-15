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
            'card'     => "tokn_test_5609gc5d58d32nr4e1g",
            'capture'  => false
        ], $this->publicKey, $this->secretKey);
    }
}
