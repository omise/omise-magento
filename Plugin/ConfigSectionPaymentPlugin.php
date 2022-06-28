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
        'key_expired_error' => 'The keys for Omise Payment are expired.',
        'locked_account_error' => 'The account is locked. Please contact support@omise.co.',
        'not_authorized' => 'An attempt was made to perform an unauthorized action.',
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

                    $errorMessage = array_key_exists($errors['code'], $this->errorCodes)
                        ? $this->errorCodes[$errors['code']]
                        : 'unable to load OmiseCapabilities api';

                    throw new LocalizedException(__($errorMessage));
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

        $standboxStatus = array_key_exists('value', $configFields['sandbox_status']) ? $configFields['sandbox_status']['value'] : $configFields['sandbox_status'];

        $publicKeyIndex = $standboxStatus ? 'test_public_key' : 'live_public_key';
        $secretKeyIndex = $standboxStatus ? 'test_secret_key' : 'live_secret_key';

        $hasPublicKeyUpdated = array_key_exists('value', $configFields[$publicKeyIndex]);
        $hasSecretKeyUpdated = array_key_exists('value', $configFields[$secretKeyIndex]);

        $this->config->setStoreId($configData['store']);

        // If keys are not updated then the CoreConfig won't have the value so we have to pull it from the config
        return [
            'public_key' => $hasPublicKeyUpdated
                ? $configFields[$publicKeyIndex]['value']
                : $this->config->getPublicKey(),
            'secret_key' => $hasSecretKeyUpdated
                ? $configFields[$secretKeyIndex]['value']
                : $this->config->getSecretKey()
        ];
    }
}
