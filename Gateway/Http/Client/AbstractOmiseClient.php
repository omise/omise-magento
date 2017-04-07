<?php
namespace Omise\Payment\Gateway\Http\Client;

use Exception;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Omise\Payment\Model\Config\Config;

abstract class AbstractOmiseClient implements ClientInterface
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
     * Omise public key
     *
     * @var string
     */
    protected $publicKey;

    /**
     * Omise secret key
     *
     * @var string
     */
    protected $secretKey;

    /**
     * @var Omise\Payment\Model\Config\Config
     */ 
    protected $config;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    public function __construct(
        Config                   $config,
        ModuleListInterface      $moduleList,
        ProductMetadataInterface $productMetadata
    ) {
        $this->config          = $config;
        $this->publicKey       = $config->getPublicKey();
        $this->secretKey       = $config->getSecretKey();

        $this->moduleList      = $moduleList;
        $this->productMetadata = $productMetadata;

        $this->defineUserAgent();
    }

    /**
     * Define configuration constant for Omise PHP library
     *
     * @return void
     */
    protected function defineUserAgent()
    {
        if (! defined('OMISE_USER_AGENT_SUFFIX')) {
            $userAgent = 'OmiseMagento/' . $this->getModuleVersion();
            $userAgent .= ' Magento/' . $this->getMagentoVersion();

            define('OMISE_USER_AGENT_SUFFIX', $userAgent);
        }
    }

    /**
     * Retrieve Magento's current version
     *
     * @return string
     */
    protected function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Retrieve Omise module's current version
     *
     * @return string
     */
    protected function getModuleVersion()
    {
        return $this->moduleList->getOne(Config::MODULE_NAME)['setup_version'];
    }

    /**
     * @param  \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     *
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        try {
            $request  = $this->request($transferObject->getBody());

            $response = [
                'object'  => 'omise',
                'status'  => self::PROCESS_STATUS_SUCCESSFUL,
                'data'    => $request,
                'message' => null,
            ];
        } catch (Exception $e) {
            $response = [
                'object'  => 'omise',
                'status'  => self::PROCESS_STATUS_FAILED,
                'data'    => null,
                'message' => $e->getMessage(),
            ];
        }

        return $response;
    }
}
