<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\OmiseMoney;
use Omise\Payment\Observer\InstallmentDataAssignObserver;
use Omise\Payment\Model\Config\Installment;
use Omise\Payment\Model\Config\Cc;

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
     * @var \Omise\Payment\Helper\OmiseHelper
     */
    private $omiseHelper;

    /**
     * @var \Omise\Payment\Model\Config\Cc
     */
    private $ccConfig;

  
    /**
     * @var OmiseMoney
     */
    private $money;

    /**
     * @param \Omise\Payment\Helper\OmiseHelper $omiseHelper
     * @param Omise\Payment\Model\Config\Cc $ccConfig
     */
    public function __construct(OmiseHelper $omiseHelper, Cc $ccConfig, OmiseMoney $money)
    {
        $this->omiseHelper = $omiseHelper;
        $this->money = $money;
        $this->ccConfig = $ccConfig;
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
        $currency = $order->getCurrencyCode();

        $requestBody = [
            self::AMOUNT      => $this->money->parse($order->getGrandTotalAmount(), $currency)->toSubunit(),
            self::CURRENCY    => $currency,
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

        if (Cc::CODE === $method->getMethod()) {
            $requestBody[self::METADATA]['secure_form_enabled'] = $this->ccConfig->getSecureForm();
        }

        return $requestBody;
    }

    public function isZeroInterestInstallment($method)
    {
        $installmentId = $method->getAdditionalInformation(InstallmentDataAssignObserver::OFFSITE);
        return ('installment_mbb' === $installmentId);
    }
}
