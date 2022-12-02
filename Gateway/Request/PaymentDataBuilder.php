<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Helper\OmiseHelper;
use Psr\Log\LoggerInterface;

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
     * @param \Omise\Payment\Helper\OmiseHelper $omiseHelper
     */
    public function __construct(OmiseHelper $omiseHelper)
    {
        $this->omiseHelper = $omiseHelper;
        // $this->logger = $logger;
    }

    /**
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        // $this->logger->debug(print_r($buildSubject, true));
        $payment = SubjectReader::readPayment($buildSubject);
        $order   = $payment->getOrder();

        $store_id = $order->getStoreId();
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $manager = $om->get(\Magento\Store\Model\StoreManagerInterface::class);
        $store_name = $manager->getStore($store_id)->getName();

        return [
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
    }
}
