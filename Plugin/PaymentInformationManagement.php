<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Omise\Payment\Plugin;

use Magento\Checkout\Api\PaymentProcessingRateLimiterInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Payment information management service.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentInformationManagement 
{
    /**
     * @var \Magento\Quote\Api\BillingAddressManagementInterface
     * @deprecated 100.1.0 This call was substituted to eliminate extra quote::save call
     */
    protected $billingAddressManagement;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var PaymentDetailsFactory
     */
    protected $paymentDetailsFactory;

    /**
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    protected $cartTotalsRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var PaymentProcessingRateLimiterInterface
     */
    private $paymentRateLimiter;

    /**
     * @param \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param PaymentDetailsFactory $paymentDetailsFactory
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository
     * @param PaymentProcessingRateLimiterInterface|null $paymentRateLimiter
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository,
        PaymentProcessingRateLimiterInterface $paymentRateLimiter = null
    ) {
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartManagement = $cartManagement;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->cartTotalsRepository = $cartTotalsRepository;
        $this->paymentRateLimiter = $paymentRateLimiter
            ?? ObjectManager::getInstance()->get(PaymentProcessingRateLimiterInterface::class);
    }


    /**
     * @inheritdoc
     */
    public function beforeSavePaymentInformation(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $logger = new \Zend\Log\Logger();
        $logger->addWriter(new \Zend\Log\Writer\Stream(BP . '/var/log/test.log'));

        $quoteRepository = $cartRepository = ObjectManager::getInstance()->get(\Magento\Quote\Api\CartRepositoryInterface::class);

        $quote = $quoteRepository->getActive($cartId);
        $quote->setAdditionalData(["paymentData"=>$paymentMethod->getData()]);
        $quote->save();

        return;
    }
}
