<?php

namespace Omise\Payment\Plugin;

use Magento\Config\Model\Config as CoreConfig;
use Omise\Payment\Model\Config\Config;
use OmiseCapabilities;
use OmiseAuthenticationFailureException;
use Omise\Payment\Helper\OmiseHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;

class ConfigSectionPaymentPlugin
{

    /**
     * @var OmiseCapabilities
     */
    private $capabilities;

    /**
     * @var Omise\Payment\Helper\OmiseHelper
     */
    private $helper;

    /**
     * @var Magento\Framework\Message\ManagerInterface;
     */
    private $messageManager;

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
     * @param OmiseHelper $helper
     * @param Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        Config $config,
        OmiseHelper $helper,
        ManagerInterface $messageManager
        )
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        // using same version as omise-php 2.13(2017-11-02)
        define('OMISE_API_VERSION', '2017-11-02');
    }

    /**
     * @param \Magento\Config\Model\Config $coreConfig
     */
    public function beforeSave(CoreConfig $coreConfig)
    {
        if ('payment' === $coreConfig->getSection()) {

            $this->config->setStoreId($coreConfig->getData('store'));
            $omiseConfigData = $coreConfig->toArray()['groups']['omise'];
            $keys = $this->getKeys($omiseConfigData);
            
            // if both keys are empty then we ignore the check.
            if ($keys['public_key'] || $keys['secret_key']) {
                try {
                    // Fetching capabilities to check the supplied keys validity
                    $this->capabilities = OmiseCapabilities::retrieve($keys['public_key'], $keys['secret_key']);

                    /** when using test mode is will fetch all available payment methods
                     *  that omise is supported
                     * */
                    $paymentList  = $this->getBackends();
                    $omiseConfigPaymentList=$this->getActivePaymentMethods($omiseConfigData);

                    // list active payment methods that not support from capabilities api
                    $nonSupportPayments = [];

                        // set disable only not support payment methods
                    $data = $coreConfig->getGroups();
                    foreach ($omiseConfigPaymentList as $payment => $title) {
                        if (! in_array($payment, $paymentList)) {
                            $data['omise']['groups'][$payment]['fields']['active']['value'] = 0;
                            array_push($nonSupportPayments, $title);
                        }
                    }

                        // show error message by using title from omise helper
                    if (! empty($nonSupportPayments)) {
                        $this->messageManager->addError(__("This Omise account is not support " . implode(", ", $nonSupportPayments)));
                    }

                        // still save other payment methods that api support
                    $coreConfig->setData('groups', $data);
                    
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
    }

    /**
     * Fetch appropriate keys by sandbox status
     *
     * @param array $configData
     */
    private function getKeys($configData)
    {
        $configFields = $configData['fields'];
            // if sandbox status is updated the updated value will be under 'value' key else it won't have the value key
        $sandboxStatus = array_key_exists('value', $configFields['sandbox_status']) 
            ? $configFields['sandbox_status']['value'] 
            : $configFields['sandbox_status'];

        $publicKeyIndex = $sandboxStatus ? 'test_public_key' : 'live_public_key';
        $secretKeyIndex = $sandboxStatus ? 'test_secret_key' : 'live_secret_key';

        $hasPublicKeyUpdated = array_key_exists('value', $configFields[$publicKeyIndex]);
        $hasSecretKeyUpdated = array_key_exists('value', $configFields[$secretKeyIndex]);

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

    /**
     * Retrieve only available backends & methods
     * from capabilities api that only support by omise plugin
     * with mapping backends id to magneto code format
     *
     * @return array
     */
    private function getBackends()
    {
        // Retrieve backends & methods from capabilities api
        $backendNames = array_map(function ($payment) {
            return key($payment);
        }, $this->capabilities['payment_backends']);
        $backendNames = array_merge($backendNames, $this->capabilities['tokenization_methods']);

        // filter not support payment method from backends list
        return array_unique(array_filter(array_map(function ($name) {
            return $this->helper->getOmiseCodeByOmiseId($name);
        }, $backendNames)));
    }

    /**
     * Retrieve active omise payment methods in magento config
     * and map omise title for displaying error message
     *
     * @param \Magento\Config\Model\Config ['groups']['omise'] $omiseConfigData
     *         
     * @return array
     */
    private function getActivePaymentMethods($configData)
    {
        $paymentConfigList = [];
        foreach ($configData['groups'] as $key => $value) {
            // filter only oayment that merchant is active
            if ($value['fields']['active']['value']) {

                /**
                 * Set payment list with display name
                 * if omise label didn't exist use title from config instead
                 */
                $paymentConfigList[$key] = $this->helper->getOmiseLabelByOmiseCode($key) ?? $this->config->getValue('title', $key);
            }
        }
        return $paymentConfigList;
    }
}


