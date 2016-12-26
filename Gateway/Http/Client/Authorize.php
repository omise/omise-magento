<?php
namespace Omise\Payment\Gateway\Http\Client;

use Omise\Payment\Gateway\Request\PaymentDataBuilder;

class Authorize extends AbstractOmiseClient
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
                'card'        => $body[PaymentDataBuilder::OMISE_TOKEN],
                'capture'     => false,
                'description' => $body[PaymentDataBuilder::ORDER_ID],
            ],
            $this->publicKey,
            $this->secretKey
        );
    }
}
