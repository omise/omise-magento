<?php

namespace Omise\Payment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Omise\Payment\Model\Api\Charge as ApiCharge;
use Omise\Payment\Model\Omise;

abstract class AbstractPayment implements ClientInterface
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
     *
     * @var string
     */
    const CHARGE_ID = 'charge_id';

    /**
     *
     * @var string
     */
    const CHARGE = 'charge';

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
        $this->omise = $omise;
        $this->apiCharge = $apiCharge;
    }

    protected function setupOmiseLib()
    {
        $this->omise->defineUserAgent();
        $this->omise->defineApiVersion();
        $this->omise->defineApiKeys();
    }

    /**
     * @param  \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     *
     * @return \Omise\Payment\Model\Api\Charge|\Omise\Payment\Model\Api\Error
     */
    abstract public function placeRequest(TransferInterface $transferObject);
}
