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
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @param \Omise\Payment\Model\Ui\OmiseConfigProvider     $config
     * @param \Magento\Framework\Module\ModuleListInterface   $moduleList
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        OmiseConfigProvider $config,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $productMetadata
    ) {
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
            $userAgent = 'OmiseMagento2/' . $this->getModuleVersion();
            $userAgent .= ' Magento2/' . $this->getMagentoVersion();

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
        return $this->moduleList->getOne(OmiseConfigProvider::MODULE_NAME)['setup_version'];
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
