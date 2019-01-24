<?php

namespace Omise\Payment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Omise\Payment\Model\Api\Charge as ApiCharge;
use Omise\Payment\Model\Omise;

class Payment implements ClientInterface
{
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
     * @var Omise\Payment\Model\Omise
     */
    protected $omise;

    /**
     * @var \Omise\Payment\Model\Api\Charge
     */
    protected $apiCharge;

    public function __construct(
        ApiCharge $apiCharge,
        Omise     $omise
    ) {
        $this->omise     = $omise;
        $this->apiCharge = $apiCharge;
    }

    /**
     * @param  \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     *
     * @return \Omise\Payment\Model\Api\Charge|\Omise\Payment\Model\Api\Error
     */
    public function placeRequest(TransferInterface $transferObject)
    {

        $this->omise->defineUserAgent();
        $this->omise->defineApiVersion();
        $this->omise->defineApiKeys();
        
        $transferObjectBody = $transferObject->getBody();

        // if charge_id already exists than action is 'manual capture'
        if (isset($transferObjectBody['charge_id'])) {
            $charge = $this->apiCharge->find($transferObjectBody['charge_id']);
            return ['charge' => $charge->capture()];
        }

        return ['charge' => $this->apiCharge->create($transferObjectBody)];
    }
}
