<?php

namespace Omise\Payment\Plugin;

use Magento\Config\Model\Config as CoreConfig;
use Omise\Payment\Model\Config\Config;
use OmiseCapabilities;
use OmiseAuthenticationFailureException;
use Magento\Framework\Exception\LocalizedException;

class ConfigSectionPaymentPlugin
{
    /**
     * Error code sent from the API and the message to be displayed on screen
     *
     * @var array
     */
    private $errorCodes = [
        'authentication_failure' => 'The keys for Omise Payment are invalid',
        'key_expired_error' => 'The keys for Omise Payment are expired.'
    ];

    /**
     * @param \Omise\Payment\Model\Config\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param \Magento\Config\Model\Config $coreConfig
     */
    public function beforeSave(CoreConfig $coreConfig)
    {
        if ('payment' === $coreConfig->getData('section')) {
            $keys = $this->getKeys($coreConfig->toArray());

            // if both keys are empty then we ignore the check.
            if ($keys['public_key'] || $keys['secret_key']) {
                try {
                    // Fetching capabilities to check the supplied keys validity
                    OmiseCapabilities::retrieve($keys['public_key'], $keys['secret_key']);
                } catch (OmiseAuthenticationFailureException $e) {
                    $errors = $e->getOmiseError();
                    throw new LocalizedException(__($this->errorCodes[$errors['code']]));
                } catch (Exception $e) {
                    throw new LocalizedException(__('unable to load OmiseCapabilities api'));
                }
            }
        }

        return [ $coreConfig ];
    }

    /**
     * Fetch appropriate keys by sandbox status
     *
     * @param array $configData
     */
    private function getKeys($configData)
    {
        $configFields = $configData['groups']['omise']['fields'];

        $publicKey = ($configFields['sandbox_status']) ? 'test_public_key' : 'live_public_key';
        $secretKey = ($configFields['sandbox_status']) ? 'test_secret_key' : 'live_secret_key';

        $this->config->setStoreId($configData['store']);

        return [
            'public_key' => array_key_exists('value', $configFields[$publicKey])
                ? $configFields[$publicKey]['value']
                : $this->config->getPublicKey(),
            'secret_key' => array_key_exists('value', $configFields[$secretKey])
                ? $configFields[$secretKey]['value']
                : $this->config->getSecretKey()
        ];
    }
}
