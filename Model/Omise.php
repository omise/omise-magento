<?php

namespace Omise\Payment\Model;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;
use Omise\Payment\Model\Config\Config;

class Omise
{
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
        $this->moduleList      = $moduleList;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param  string $public_key
     * @param  string $secret_key
     *
     * @return void
     */
    public function defineApiKeys($public_key = '', $secret_key = '')
    {
        if (! defined('OMISE_PUBLIC_KEY')) {
            define('OMISE_PUBLIC_KEY', $public_key ? $public_key : $this->config->getPublicKey());
        }

        if (! defined('OMISE_SECRET_KEY')) {
            define('OMISE_SECRET_KEY', $secret_key ? $secret_key : $this->config->getSecretKey());
        }
    }

    /**
     * @param  string $version
     *
     * @return void
     */
    public function defineApiVersion($version = '2017-11-02')
    {
        if (! defined('OMISE_API_VERSION')) {
            define('OMISE_API_VERSION', $version);
        }
    }

    /**
     * Define configuration constant for Omise PHP library
     *
     * @return void
     */
    public function defineUserAgent()
    {
        if (! defined('OMISE_USER_AGENT_SUFFIX')) {
            define(
                'OMISE_USER_AGENT_SUFFIX',
                sprintf(
                    'OmiseMagento/%s Magento/%s',
                    $this->getModuleVersion(),
                    $this->getMagentoVersion()
                )
            );
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
}
