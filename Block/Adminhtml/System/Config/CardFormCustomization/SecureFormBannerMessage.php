<?php

namespace Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization;

use Magento\Framework\Notification\MessageInterface;
use Omise\Payment\Model\Config\Cc as OmiseCcConfig;

class SecureFormBannerMessage implements MessageInterface
{

    /**
     * Message identity
     */
    const MESSAGE_IDENTITY = 'opn_payments_secure_form_message';

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
        return self::MESSAGE_IDENTITY;
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
        return __('<b>Opn Payments</b> : 
            Update now to use the secure form to securely accept payment information. 
            Note that you must re-customize the credit card checkout form after the upgrade. 
            For more details, please click <a href="https://www.omise.co/magento-plugin">here</a>.
        ');
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
}
