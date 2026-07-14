<?php

namespace Omise\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Helper\OmiseMoney;
use Omise\Payment\Helper\OmiseHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\Locale\Resolver;
use Magento\Store\Model\StoreManagerInterface;

class UPAPaymentDataBuilder implements BuilderInterface
{
    /**
     * @var Resolver
     */
    private $localeResolver;

    /**
     * @var OmiseMoney
     */
    private $money;

    /**
     * @var OmiseHelper
     */
    private $omiseHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param OmiseMoney $money
     * @param OmiseHelper $omiseHelper
     * @param Resolver $localeResolver
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        OmiseMoney $money,
        OmiseHelper $omiseHelper,
        Resolver $localeResolver,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder
    ) {
        $this->money = $money;
        $this->omiseHelper = $omiseHelper;
        $this->localeResolver = $localeResolver;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
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
        $methodCode = $payment->getPayment()->getMethod();
        $currency = $order->getCurrencyCode();
        $store = $this->storeManager->getStore($order->getStoreId());
        $methodId = $this->omiseHelper->getMethodId($methodCode);

        $upaThemeColor = $this->omiseHelper->getConfig('upa_theme_color', $order->getStoreId());
        $upaTextColor = $this->omiseHelper->getConfig('upa_text_color', $order->getStoreId());

        $locale = $this->localeResolver->getLocale();

        if (empty($methodId)) {
            return [];
        }
        $payload = [
            'amount' => $this->money->setAmountAndCurrency(
                $order->getGrandTotalAmount(),
                $currency
            )->toSubunit(),
            'currency'        => $currency,
            'order_id'        => (string) $order->getOrderIncrementId(),
            'description'     => 'Magento Order id ' . $order->getOrderIncrementId(),
            'payment_methods' => [$methodId],
            'redirect_urls'   => [
                'complete_url' => $this->urlBuilder->getUrl(
                    'omise/callback/upacallback',
                    ['_secure' => true]
                ),
                'cancel_url'   => $this->urlBuilder->getUrl(
                    'omise/payment/cancel',
                    ['_secure' => true]
                ),
            ],
            'metadata'        => [
                'order_id'  => (string) $order->getOrderIncrementId(),
                'store_id' => $order->getStoreId(),
                'store_name' => $store->getName()
            ],
            "is_upa" => true
        ];

        
        $payload['style'] = [
            'theme_color'  => $upaThemeColor,
            'text_color'  => $upaTextColor
        ];
        
        $locale = substr(strtolower($locale), 0, 2);
        if (!empty($locale)) {
            $payload['locale'] = $locale;
        }
        return $payload;
    }
}
