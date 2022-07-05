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

    private $logger;

    private $capabilities;
    /**
     * @var Omise\Payment\Helper\OmiseHelper
     */
    private $helper;
    
    private $messageManager;
    /**
     * @var WriterInterface
     */
    protected $configWriter;

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
     */
    public function __construct(
        Config $config,
        OmiseHelper $helper, 
        \Psr\Log\LoggerInterface $logger,
        ManagerInterface $messageManager)
    {
        $this->config = $config;
        $this->logger = $logger;
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
                    // print_r($this->getBackendsWithOmiseCode());
                            // print_r($this->capabilities['payment_backends']);

                    $paymentList  = $this->getBackends();
                    // print_r($paymentList);

                    $omiseConfigPaymentList=$this->getBackendsTest($omiseConfigData);
                    
                    //TODO 
                    $nonSupportPayments = array();
                    $data = $coreConfig->getGroups();
                    foreach ($omiseConfigPaymentList as $payment => $title) {
                        if(!in_array($payment, $paymentList)){
                            $data['omise']['groups'][$payment]['fields']['active']['value']=0;
                            array_push($nonSupportPayments,$title);
                        }
                        
                    }
                    if(!empty($nonSupportPayments)){
                        $this->messageManager->addError(__("The Omise account is not support ".implode(", ", $nonSupportPayments)));
                    }
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
        $sandboxStatus = array_key_exists('value', $configFields['sandbox_status']) ? $configFields['sandbox_status']['value'] : $configFields['sandbox_status'];

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

    private function getBackends()
    {
        $backendNames = array_map(function ($payment) {return key($payment);}, $this->capabilities['payment_backends']);
        $backendNames =array_merge($backendNames, $this->capabilities['tokenization_methods']);
        return array_filter(array_map(function ($name) {
            return $this->helper->getOmiseCodeByOmiseName($name);
        }, $backendNames));
    }

    private function getBackendsTest($configData)
    {
        $paymentConfigList = []; 
        foreach ($configData['groups'] as $key => $value) {
            //filter only oayment that merchant is active
            if($value['fields']['active']['value']){
                /* set list with display name
                 * if label didn't exist use title from config instead
                 */ 
                $paymentConfigList[$key] = $this->helper->getOmiseLabelByOmiseCode($key) ?? $this->config->getValue('title', $key );
            }
        }
        return  $paymentConfigList;
    }
        /**
     *
     * @return array|null
     */
    public function getBackendsWithOmiseCode()
    {
        // $list = array();

        //    $paymentConfigList = []; 
        // foreach ($configData['groups'] as $backend) {

        // }
       return  array_map(function ($backend) {
           $backend['code']=$this->helper->getOmiseCodeByOmiseName(key($backend));
           return $backend;
        }, $this->capabilities['payment_backends']);
    }

}


