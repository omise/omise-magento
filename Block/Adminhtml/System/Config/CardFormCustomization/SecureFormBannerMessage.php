<?php

namespace Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization;

use Magento\Framework\Notification\MessageInterface;
use Omise\Payment\Model\Config\Cc as OmiseCcConfig;

class SecureFormBannerMessage implements MessageInterface
{

    /**
     * @var \Omise\Payment\Model\Config\Cc
     */
    protected $omiseCcConfig;

    /**
     * @var Omise\Payment\Model\Customer
     */
    protected $customer;

    public function __construct(
        OmiseCcConfig   $omiseCcConfig
    ) {
        $this->omiseCcConfig   = $omiseCcConfig;
    }

    /**
     * Retrieve unique system message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return 'opn_payments_secure_form_message';
    }

    /**
     * Whether the system message should be shown
     *
     * @return bool
     */
    public function isDisplayed()
    {
        $secureForm = $this->omiseCcConfig->getSecureForm();
        return $secureForm == 'yes' ? false : true;
    }

    /**
     * Retrieve system message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        $defaultMessage = "<b>Opn Payments</b> : Update your plugin to the latest version to enable 
        Secure Form and maximize the security of your customers’ information. 
        You will need to re-customize the credit card checkout form after the upgrade. 
        <a target='blank' href='https://www.omise.co/magento-plugin'>
        Learn how to enable Secure Form.</a>";

        return $this->localize('secure_form_banner_message', $defaultMessage);
    }

    /**
     * Retrieve system message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }

    /**
     * localize using translation key
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    public function localize($key, $default)
    {
        $result = __($key);
        return $result == $key ? $default : $result;
    }
}
