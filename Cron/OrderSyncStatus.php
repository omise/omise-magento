<?php
namespace Omise\Payment\Cron;

use Exception;

class OrderSyncStatus 
{
    private $order;
    private $paymentMethodArray = [
                                "omise_cc",
                                "omise_offline_conveniencestore",
                                "omise_offline_paynow",
                                "omise_offline_promptpay",
                                "omise_offline_tesco",
                                "omise_offsite_alipay",
                                "omise_offsite_truemoney"
                            ];

    private $orderStatusArray = ['pending_payment', 'processing'];

    private $orderRepository;

    private $refreshCounter = 1;

    protected $timezone;

    private $config;

    /**
     * @var \Omise\Payment\Model\Api\Charge
     */
    protected $apiCharge;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Omise\Payment\Model\Api\Charge $apiCharge,
        //\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Omise\Payment\Model\Config\Config $config
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->apiCharge = $apiCharge;
        //$this->timezone = $timezone;
        $this->config = $config;
    }

    /**
     * Get the list of orders to be sync the status
     */
    public function execute()
    {
        $orderIds = $this->getOrderIds();
        //$date = $this->timezone->formatDateTime();
        $date = strtotime("now");
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron_new.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info("mayur : ".$date);

        if (!empty($orderIds)) {
            foreach ($orderIds as $order) {
                $logger->info("mayur : ".$order['entity_id']);
                $this->loadOrder($order['entity_id']);
                $expiryDate = $this->refreshExpiryDate();
                //$this->updateOrderStatus($expiryDate);;
            }
        }
        return $this;
    }

    /**
     * Get the list of orders to be Sync.
     *
     * @return array List of order IDs
     */
    public function getOrderIds()
    {
        $orderStatus = implode(',', $this->orderStatusArray);
        $paymentMethods = implode(',', $this->paymentMethodArray);
        $orders = $this->_orderCollectionFactory->create();
        $orders->distinct(true);
        $orders->addFieldToSelect(['entity_id','increment_id','created_at']);
        $orders->addFieldToFilter('main_table.status', ['in' => $orderStatus]);
        $orders->addFieldToFilter('sop.method', ['in' => $paymentMethods]);
        $orders->join(['sop' => 'sales_order_payment'], 'sop.parent_id=main_table.entity_id', '');
        $orders->addAttributeToSort('entity_id', 'desc')->setPageSize(2)->setCurPage(1);
        $orderIds = $orders->getData();
        return $orderIds;
    }

    private function loadOrder($orderId)
    {
        $this->order = $this->orderRepository->get($orderId);
    }

    private function refreshExpiryDate()
    {
        $payment    = $this->order->getPayment();
        $chargeId   = $payment->getAdditionalInformation('charge_id');
        $expiryDate = $payment->getAdditionalInformation('omise_expiry_date');
        if(!isset($expiryDate) && isset($chargeId) && $this->refreshCounter > 0) {
            //$charge = $this->apiCharge->find($chargeId);
            $charge = \OmiseCharge::retrieve($chargeId, $this->config->getPublicKey(), $this->config->getSecretKey());
            $expiryDate = $charge['expires_at'];
            $payment->setAdditionalInformation('omise_expiry_date', $expiryDate);
            $this->refreshCounter--;
        }
        return $expiryDate;
    }

    private function updateOrderStatus($expiryDate)
    {
        if($expiryDate){
           
        }
        
    }
}
