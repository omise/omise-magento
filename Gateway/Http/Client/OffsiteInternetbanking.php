<?php
namespace Omise\Payment\Gateway\Http\Client;

use Omise\Payment\Gateway\Request\PaymentDataBuilder;
use Omise\Payment\Gateway\Request\PaymentOffsiteBuilder;

class OffsiteInternetbanking extends AbstractOmiseClient
{
    /**
     * @param  array $body
     *
     * @return array
     */
    public function request(Array $body)
    {
        return \OmiseCharge::create(
            [
                'amount'      => $body[PaymentDataBuilder::AMOUNT],
                'currency'    => $body[PaymentDataBuilder::CURRENCY],
                'description' => $body[PaymentDataBuilder::DESCRIPTION],
                'offsite'     => $body[PaymentOffsiteBuilder::OFFSITE],
            ],
            $this->publicKey,
            $this->secretKey
        );
    }
}
