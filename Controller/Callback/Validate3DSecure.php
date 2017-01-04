<?php
namespace Omise\Payment\Controller\Callback;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Omise\Payment\Model\OmiseConfig;
use Omise\Payment\Model\PaymentMethodManagement;

class Validate3DSecure extends Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var \Omise\Payment\Model\OmiseConfig
     */
    protected $omiseConfig;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session       $session
     * @param \Omise\Payment\Model\OmiseConfig      $omiseConfig
     */
    public function __construct(
        Context $context,
        Session $session,
        OmiseConfig $omiseConfig
    ) {
        parent::__construct($context);

        $this->session     = $session;
        $this->omiseConfig = $omiseConfig;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $order          = $this->session->getLastRealOrder();

        if ($order->getId()) {
            try {
                $payment = $order->getPayment();

                $this->validatePayment($payment);
                $this->validateOmiseChargeId($payment);

                $charge = \OmiseCharge::retrieve(
                    $payment->getAdditionalInformation('omise_charge_id'),
                    $this->omiseConfig->getPublicKey(),
                    $this->omiseConfig->getSecretKey()
                );

                $this->validateCharge($charge);

                return $resultRedirect->setPath('checkout/onepage/success', ['_secure' => true]);
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, $e->getMessage());

                $this->session->restoreQuote();

                return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
            }
        }

        $this->messageManager->addErrorMessage(__('Cannot process 3-D Secure validation, record not found. Please check your order or contact our support.'));

        return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
    }

    /**
     * @param  \Magento\Sales\Model\Order\Payment $payment
     *
     * @return void
     *
     * @throws \Exception if payment method is not 'omise'
     */
    protected function validatePayment($payment)
    {
        if ($payment->getMethod() !== "omise") {
            throw new Exception("Invalid payment.");
        }
    }

    /**
     * @param  \Magento\Sales\Model\Order\Payment $payment
     *
     * @return void
     *
     * @throws \Exception if it cannot retrieve 'omise_charge_id' from a payment.
     */
    protected function validateOmiseChargeId($payment)
    {
        if (! $payment->getAdditionalInformation('omise_charge_id')) {
            throw new Exception("Invalid charge id.");
        }
    }

    /**
     * @param \OmiseCharge $charge
     */
    protected function validateCharge($charge)
    {
        if ($charge['capture'] === false) {
            // Validate for 'authorize only' action
            $this->validateChargeWasAuthorized($charge);

        } else if ($charge['capture'] === true) {
            // Validate for 'authorize & capture' action
            $this->validateChargeWasPaid($charge);

        } else {
            // In case something goes wrong with 'charge.capture' param (it doesn't return boolean value properly).
            // It will validate by assumed that 'payment action' is set to 'authorize & capture'.
            $this->validateChargeWasPaid($charge);
        }
    }

    /**
     * @param \OmiseCharge $charge
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function validateChargeWasAuthorized($charge)
    {
        if ($charge['authorized'] === true) {
            return true;
        }

        throw new Exception("Failed 3-D Secure validation (".$charge['id']." was not authorized)");
    }

    /**
     * @param \OmiseCharge $charge
     */
    protected function validateChargeWasPaid($charge)
    {
        $this->validateChargeWasAuthorized($charge);

        // support Omise API version '2014-07-27' by checking if 'captured' exist.
        $paid = isset($charge['captured']) ? $charge['captured'] : $charge['paid'];
        if ($paid === true) {
            return true;
        }

        throw new Exception("Failed 3-D Secure validation (".$charge['id']." was not paid)");
    }
}
