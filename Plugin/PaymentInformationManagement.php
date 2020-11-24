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
        \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository,
        PaymentProcessingRateLimiterInterface $paymentRateLimiter = null
    ) {
        $this->billingAddressManagement = $billingAddressManagement;
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

        $quoteRepository = $this->getCartRepository();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $quoteRepository->getActive($cartId);
        $paymentData = $paymentMethod->getData();
        
        
        $logger->info("this is returning:");
        $logger->info(json_encode($paymentData));


        $paymentInfo = $quote->getPayment()->getData();


        $logger->info(json_encode($paymentInfo));
        

        return;

        $customerId = $quote->getBillingAddress()
            ->getCustomerId();
        if (!$billingAddress->getCustomerId() && $customerId) {
            //It's necessary to verify the price rules with the customer data
            $billingAddress->setCustomerId($customerId);
        }

        $quote->setDataChanges(true);
        $shippingAddress = $quote->getShippingAddress();
        
        if ($shippingAddress && $shippingAddress->getShippingMethod()) {
            $shippingRate = $shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod());
            if ($shippingRate) {
                $shippingAddress->setLimitCarrier($shippingRate->getCarrier());
            }
        }
        //$this->getLogger()->debug(var_export($paymentMethod, true));
        $this->paymentMethodManagement->set($cartId, $paymentMethod);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentInformation($cartId)
    {
        /** @var \Magento\Checkout\Api\Data\PaymentDetailsInterface $paymentDetails */
        $paymentDetails = $this->paymentDetailsFactory->create();
        $paymentDetails->setPaymentMethods($this->paymentMethodManagement->getList($cartId));
        $paymentDetails->setTotals($this->cartTotalsRepository->get($cartId));

        return $paymentDetails;
    }

    /**
     * Get logger instance
     *
     * @return \Psr\Log\LoggerInterface
     * @deprecated 100.1.8
     */
    private function getLogger()
    {
        if (!$this->logger) {
            $this->logger = ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        }
        return $this->logger;
    }

    /**
     * Get Cart repository
     *
     * @return \Magento\Quote\Api\CartRepositoryInterface
     * @deprecated 100.2.0
     */
    private function getCartRepository()
    {
        if (!$this->cartRepository) {
            $this->cartRepository = ObjectManager::getInstance()
                ->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        }
        return $this->cartRepository;
    }
}
