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
        $text = '<b>Opn Payments</b> : ';
        $text .= __('Update now to use the secure form to securely accept payment information.') . ' ';
        $text .= __('Note that you must re-customize the credit card checkout form after the upgrade.') . ' ';
        $text .= __(
            'For more details, please click %1 here %2.',
            '<a target="blank" href="https://www.omise.co/magento-plugin">',
            '</a>'
        );
        return $text;
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
