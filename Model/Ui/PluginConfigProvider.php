<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Omise\Payment\Model\Config\Config;

class PluginConfigProvider implements ConfigProviderInterface
{
    private $config;
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'isOmiseSandboxOn' => $this->config->isSandboxEnabled()
        ];
    }
}
