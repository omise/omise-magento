<?php
namespace Omise\Payment\Gateway\Http\Client;

use Omise\Payment\Gateway\Request\PaymentDataBuilder;
use Omise\Payment\Gateway\Request\ThreeDSecureDataBuilder;

class Authorize extends AbstractOmiseClient
{
    /**
     * @param  array $body
     *
     * @return array
     */
    public function request(Array $body)
    {
        $params = [
            'amount'      => $body[PaymentDataBuilder::AMOUNT],
            'currency'    => $body[PaymentDataBuilder::CURRENCY],
            'card'        => $body[PaymentDataBuilder::OMISE_TOKEN],
            'capture'     => false,
            'description' => $body[PaymentDataBuilder::ORDER_ID],
        ];

        if ($body[ThreeDSecureDataBuilder::PROCESS_3DSECURE]) {
            $params['return_uri'] = $body[ThreeDSecureDataBuilder::RETURN_URI];
        }

        return \OmiseCharge::create($params, $this->publicKey, $this->secretKey);
    }
}
