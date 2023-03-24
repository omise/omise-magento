<?php

namespace Omise\Payment\Controller\Callback\Traits;

trait FailedChargeTrait
{
    /**
     * @param boolean $shopeePayFaild This is temprorary param
     * @param string $errorMessage
     */
    public function processFailedCharge($errorMessage, $shopeePayFailed)
    {
        // if payment method is shopee pay then the charge is neither success or failed
        // then we should not trigger webhook because charge.failed webhook is not triggered
        if (!$shopeePayFailed && $this->config->isWebhookEnabled()) {
            $this->logger->debug($errorMessage);
            $this->messageManager->addErrorMessage($errorMessage);
            return $this->redirect(self::PATH_CART);
        }

        // If webhook is not enabled then this will
        // 1. Cancel the order
        // 2. Set the error message to display in cart page
        // 3. Log the error message
        throw new \Magento\Framework\Exception\LocalizedException($errorMessage);
    }
}
