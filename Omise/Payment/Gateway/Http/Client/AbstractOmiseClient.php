<?php

namespace Omise\Payment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Omise\Payment\Model\Ui\OmiseConfigProvider;

abstract class AbstractOmiseClient implements ClientInterface
{
    /**
     * Client request status represented to initiating step.
     *
     * @var string
     */
    const PROCESS_STATUS_INIT = 'initiate_request';

    /**
     * Client request status represented to successful request step.
     *
     * @var string
     */
    const PROCESS_STATUS_SUCCESSFUL = 'successful';

    /**
     * Client request status represented to failed request step.
     *
     * @var string
     */
    const PROCESS_STATUS_FAILED = 'failed';

    protected $publicKey;
    protected $secretKey;

    public function __construct(OmiseConfigProvider $config)
    {
        $this->publicKey = $config->getPublicKey();
        $this->secretKey = $config->getSecretKey();
    }

    /**
     * @param  \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     *
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $body = $transferObject->getBody();
        $payload  = [
            'omise_card_token' => $body['omise_card_token']
        ];

        $response = [
            'status' => self::PROCESS_STATUS_INIT,
            'api'    => null
        ];
        
        try {
            $response['api']    = $this->request($payload);
            $response['status'] = self::PROCESS_STATUS_SUCCESSFUL;
        } catch (Exception $e) {
            $response['status'] = self::PROCESS_STATUS_FAILED;
        }

        return $response;
    }
}
