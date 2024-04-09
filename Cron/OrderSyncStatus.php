<?php
namespace Omise\Payment\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Exception;

class OrderSyncStatus
{
    /**
     * @var
     */
    private $order;

    /**
     * @var array
     */
    private $paymentMethodArray = [
        "omise_cc",
        "omise_cc_googlepay",
        "omise_offline_conveniencestore",
        "omise_offline_paynow",
        "omise_offline_promptpay",
        "omise_offline_tesco",
        "omise_offsite_alipay",
        "omise_offsite_truemoney",
        "omise_offsite_installment",
        "omise_offsite_alipaycn",
        "omise_offsite_alipayhk",
        "omise_offsite_dana",
        "omise_offsite_gcash",
        "omise_offsite_kakaopay",
        "omise_offsite_touchngo",
        "omise_offsite_internetbanking",
        "omise_offsite_mobilebanking",
        "omise_offsite_rabbitlinepay",
        "omise_offsite_ocbcpao",
        "omise_offsite_ocbc_digital",
        "omise_offsite_grabpay",
        "omise_offsite_boost",
        "omise_offsite_duitnowobw",
        "omise_offsite_duitnowqr",
        "omise_offsite_maybankqr",
        "omise_offsite_shopeepay",
        "omise_offsite_atome",
        "omise_offsite_paypay",
        "omise_offiste_wechat_pay"
    ];

    /**
     * @var array
     */
    private $orderStatusArray = ['pending_payment', 'processing'];

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var int
     */
    private $lastProcessedOrderId = 0;

    /**
     * @var int
     */
    private $refreshCounter = 20;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Omise\Payment\Model\SyncStatus
     */
    private $syncStatus;

    /**
     * @var \Omise\Payment\Model\Api\Charge
     */
    protected $apiCharge;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @var \Omise\Payment\Model\Config\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    private $cacheFrontendPool;
    
    /**
     * Constructor
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Omise\Payment\Model\Api\Charge $apiCharge
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Omise\Payment\Model\SyncStatus $syncStatus
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Omise\Payment\Model\SyncStatus $syncStatus,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Omise\Payment\Model\Config\Config $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        $this->syncStatus = $syncStatus;
        $this->configWriter = $configWriter;
        $this->config = $config;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * Get the list of orders to be sync the status
     * @throws Exception
     */
    public function execute()
    {
        $isCronEnabled = (bool)$this->config->getValue('enable_cron_autoexpirysync');

        if ($isCronEnabled) {
            try {
                $this->sync();
            } catch (\Exception $e) {
                if (isset($this->lastProcessedOrderId)) {
                    $this->saveLastOrderId();
                }
            }
        }
        return $this;
    }

    /**
     * @return void
     */
    private function sync()
    {
        $this->lastProcessedOrderId = $this->scopeConfig->getValue(
            'payment/omise/cron_last_order_id'
        );

        $orderIds = $this->getOrderIds();

        if (!empty($orderIds)) {
            foreach ($orderIds as $order) {
                $this->lastProcessedOrderId = $order['entity_id'];
                $this->order = $this->orderRepository->get($order['entity_id']);

                // set the store to fetch configuration values from store specific to the order
                $this->config->setStoreId($this->order->getStore()->getId());

                $isExpired = $this->isExpired();

                if ($isExpired) {
                    $this->syncStatus->cancelOrderInvoice($this->order);
                    $this->order
                        ->registerCancellation(__('Opn Payments: Payment expired. (cron job sync).'))
                        ->save();
                }
            }
        } else {
            $this->lastProcessedOrderId = 0;
        }

        $this->saveLastOrderId();
    }

    /**
     * Get the list of orders to be Sync.
     *
     * @return array List of order IDs
     */
    public function getOrderIds()
    {
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSort('entity_id', 'desc')
            ->setPageSize($this->refreshCounter)
            ->setCurPage(1);

        $collection->getSelect()
            ->join(
                ['sop' => $collection->getTable('sales_order_payment')],
                'sop.parent_id = main_table.entity_id',
                ['method']
            )
                ->where('main_table.status in (?)', $this->orderStatusArray)
                ->where('sop.method in (?)', $this->paymentMethodArray);
        if (isset($this->lastProcessedOrderId) && (int) $this->lastProcessedOrderId) {
            $collection->getSelect()->where('main_table.entity_id < ?', $this->lastProcessedOrderId);
        }

        return $collection->getData();
    }

    /**
     * isExpired
     *  - Gets fresh charge data and returns bool representing if the charge has expired or not
     * @return bool
     */
    private function isExpired()
    {
        $isExpired = true;
        $payment    = $this->order->getPayment();
        $chargeId   = $payment->getAdditionalInformation('charge_id');

        if (isset($chargeId) && $this->refreshCounter > 0) {
            $this->charge = \OmiseCharge::retrieve(
                $chargeId,
                $this->config->getPublicKey(),
                $this->config->getSecretKey()
            );
            $isExpired = $this->charge['expired'];
            $this->refreshCounter--;
        }

        return (bool)$isExpired;
    }

    /**
     * @return void
     */
    public function saveLastOrderId()
    {
        if (isset($this->lastProcessedOrderId)) {
            $this->configWriter->save(
                'payment/omise/cron_last_order_id',
                $this->lastProcessedOrderId
            );
            $this->cacheTypeList->cleanType('config');

            foreach ($this->cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
        }
    }
}
