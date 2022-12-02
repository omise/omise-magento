<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Observer\InstallmentDataAssignObserver;
use Omise\Payment\Model\Config\Installment;

class PaymentDataBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    const AMOUNT = 'amount';

    /**
     * @var string
     */
    const CURRENCY = 'currency';
    
    /**
     * @var string
     */
    const DESCRIPTION = 'description';

    /**
     * @var string
     */
    const METADATA = 'metadata';

    /**
     * @var string
     */
    const ZERO_INTEREST_INSTALLMENTS = 'zero_interest_installments';

    /**
     * @param \Omise\Payment\Helper\OmiseHelper $omiseHelper
     */
    public function __construct(OmiseHelper $omiseHelper)
    {
        $this->omiseHelper = $omiseHelper;
    }

    /**
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $order   = $payment->getOrder();
        $method  = $payment->getPayment();

        $store_id = $order->getStoreId();
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $manager = $om->get(\Magento\Store\Model\StoreManagerInterface::class);
        $store_name = $manager->getStore($store_id)->getName();

        $requestBody = [
            self::AMOUNT      => $this->omiseHelper->omiseAmountFormat(
                $order->getCurrencyCode(),
                $order->getGrandTotalAmount()
            ),
            self::CURRENCY    => $order->getCurrencyCode(),
            self::DESCRIPTION => 'Magento 2 Order id ' . $order->getOrderIncrementId(),
            self::METADATA    => [
                'order_id' => $order->getOrderIncrementId(),
                'store_id' => $order->getStoreId(),
                'store_name' => $store_name
            ]
        ];

        if (Installment::CODE === $method->getMethod()) {
            $requestBody[self::ZERO_INTEREST_INSTALLMENTS] = $this->isZeroInterestInstallment($method);
        }

        return $requestBody;
    }

    public function isZeroInterestInstallment($method)
    {
        $installmentId = $method->getAdditionalInformation(InstallmentDataAssignObserver::OFFSITE);
        return ('installment_mbb' === $installmentId);
    }
}
