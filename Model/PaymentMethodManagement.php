<?php
namespace Omise\Payment\Model;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\PaymentException;
use Omise\Payment\Api\PaymentMethodManagementInterface;
use Omise\Payment\Model\OmiseConfig;
use Omise\Payment\Model\Data\OmiseCharge as DataOmiseCharge;

class PaymentMethodManagement implements PaymentMethodManagementInterface
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
     * @param \Magento\Checkout\Model\Session  $session
     * @param \Omise\Payment\Model\OmiseConfig $omiseConfig
     */
    public function __construct(
        Session $session,
        OmiseConfig $omiseConfig
    ) {
        $this->session     = $session;
        $this->omiseConfig = $omiseConfig;
    }

    /**
     * @param  int $orderId
     *
     * @return string
     */
    public function get3DSecureAuthorizeUri($orderId)
    {
        try {
            $order = $this->session->getLastRealOrder();

            if ($order->getId() && $orderId == $order->getId()) {
                $payment = $order->getPayment();

                $charge = \OmiseCharge::retrieve(
                    $payment->getAdditionalInformation('omise_charge_id'),
                    $this->config->getPublicKey(),
                    $this->config->getSecretKey()
                );

                return $charge[DataOmiseCharge::AUTHORIZE_URI];
            }

            throw new Exception("Order not found, please contact our support.");
        } catch (Exception $e) {
            throw new PaymentException(
                __($e->getMessage()),
                $e
            );
        }
    }
}