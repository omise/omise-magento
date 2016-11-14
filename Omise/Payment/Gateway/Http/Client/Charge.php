<?php

namespace Omise\Payment\Gateway\Http\Client;

class Charge extends AbstractOmiseClient
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
            'card'     => "tokn_test_55zygut6s3y0s91k0n5"
        ]);
    }
}
