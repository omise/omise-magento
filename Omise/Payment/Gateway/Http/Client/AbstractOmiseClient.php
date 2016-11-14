<?php

namespace Omise\Payment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

define('OMISE_PUBLIC_KEY', 'pkey_test_51fl8dfabqmt3m27vl7');
define('OMISE_SECRET_KEY', 'skey_test_51fl8dfabe7sqnj8th2');

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

    /**
     * @param  \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     *
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $payload  = [];
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
