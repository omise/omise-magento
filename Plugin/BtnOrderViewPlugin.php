<?php

namespace Omise\Payment\Plugin;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Omise\Payment\Helper\OmiseHelper as Helper;

class BtnOrderViewPlugin
{
    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Undocumented function
     *
     * @param UrlInterface $backendUrl
     * @param Http $request
     * @param ScopeConfigInterface $scopeConfig
     * @param Helper $helper
     */
    public function __construct(
        UrlInterface $backendUrl,
        Http $request,
        ScopeConfigInterface $scopeConfig,
        Helper $helper
    ) {
        $this->backendUrl = $backendUrl;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
    }

    /**
     * @todo check payment method to add this button
     * @param \Magento\Sales\Block\Adminhtml\Order\View $subject
     * @return void
     */
    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $subject)
    {
        $order = $subject->getOrder();
        if ($this->helper->canOrderStatusAutoSync($order)) {
            $autoSyncAction = $this->backendUrl->getUrl('omise/ordersync/order_id/'.$order->getId());
            $autoSyncAction = $subject->getUrl(
                'omise/ordersync/',
                ['id'=> $subject->getRequest()->getParam('order_id')]
            );
            $subject->addButton(
                'sync_order_status',
                [
                    'label' => __('Sync Order Status'),
                    'onclick' => 'setLocation(\'' . $autoSyncAction . '\')',
                    'class' => 'action-default scalable',
                    'title' => 'Sync order status for omise payment methods'
                ]
            );
        }
        return null;
    }
}
