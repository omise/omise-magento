<?php
namespace Omise\Payment\Gateway\Http\Client;

use Exception;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

use Omise\Payment\Model\Config\Config;
use Omise\Payment\Model\Omise;
use Omise\Payment\Model\Api\Charge as ApiCharge;


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
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $this->omise->defineUserAgent();
        $this->omise->defineApiVersion();
        $this->omise->defineApiKeys();

        $charge = $this->apiCharge->create($transferObject->getBody());

        if ($charge instanceof \Omise\Payment\Model\Api\Error) {
            return [
                'object'  => 'omise',
                'status'  => self::PROCESS_STATUS_FAILED,
                'data'    => null,
                'message' => $charge->getMessage()
            ];
        }

        return [
            'object'  => 'omise',
            'status'  => self::PROCESS_STATUS_SUCCESSFUL,
            'data'    => $charge->getObject(),
            'message' => null
        ];
    }
}
