<?php
namespace Omise\Payment\Model;

use Exception;
use Magento\Checkout\Model\Session;
use Omise\Payment\Api\Data\OmiseChargeInterface;
use Omise\Payment\ModOmiseConfig
class OmiseCharge implements OmiseChargeInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * Omise public key
     *
     * @var string
     */
    protected $publicKey;

    /**
     * Omise secret key
     *
     * @var string
     */
    protected $secretKey;

    /**
     * @param \Omise\Payment\Model\OmiseConfig $config
     * @param \Magento\Checkout\Model\Session  $session
     */
    public function __construct(
        OmiseConfig $config,
        Session $session
    ) {
        $this->publicKey = $config->getPublicKey();
        $this->secretKey = $config->getSecretKey();
        $this->session   = $session;
    }

    /**
     * Gets charge's authorize uri.
     *
     * @return string|null
     */
    public function getAuthorizeUri($orderId)
    {
        $order = $this->session->getLastRealOrder();

        if ($order->getId() && $orderId === $order->getId()) {
            try {
                $payment = $order->getPayment();

                $this->validatePayment($payment);
                $this->validateOmiseChargeId($payment);

                $charge = \OmiseCharge::retrieve(
                    $payment->getAdditionalInformation('omise_charge_id'),
                    $this->publicKey,
                    $this->secretKey
                );

                return $charge[self::AUTHORIZE_URI];
            } catch (Exception $e) {

            }
        }
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
}