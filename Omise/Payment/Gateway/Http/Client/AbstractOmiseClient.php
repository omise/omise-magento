<?php

namespace Omise\Payment\Gateway\Http\Client;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;
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

    protected $moduleList;
    protected $productMetadata;

    public function __construct(
        OmiseConfigProvider $config,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $productMetadata
    ) {
        $this->publicKey = $config->getPublicKey();
        $this->secretKey = $config->getSecretKey();

        $this->moduleList = $moduleList;
        $this->productMetadata = $productMetadata;

        $this->defineUserAgent();
    }

    protected function defineUserAgent()
    {
        if (!defined('OMISE_USER_AGENT_SUFFIX')) {
            $userAgent = 'OmiseMagento2/' . $this->getModuleVersion();
            $userAgent .= ' Magento2/' . $this->getMagentoVersion();

            define('OMISE_USER_AGENT_SUFFIX', $userAgent);
        }
    }

    protected function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }

    protected function getModuleVersion()
    {
        return $this->moduleList->getOne(OmiseConfigProvider::MODULE_NAME)['setup_version'];
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
