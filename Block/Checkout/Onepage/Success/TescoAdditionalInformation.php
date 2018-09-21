<?php
namespace Omise\Payment\Block\Checkout\Onepage\Success;

class TescoAdditionalInformation extends \Magento\Framework\View\Element\Template
{
    /**
    * Recipient email config path
    */
    const XML_PATH_EMAIL_RECIPIENT = 'test/email/send_email';
    /**
    * @var \Magento\Framework\Mail\Template\TransportBuilder
    */
    protected $_transportBuilder;
    
    /**
    * @var \Magento\Framework\Translate\Inline\StateInterface
    */
    protected $inlineTranslation;
    
    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;
    
    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $storeManager;
    
    /**
    * @var \Magento\Framework\Escaper
    */
    protected $_escaper;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    private $log;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Escaper $escaper,
        \PSR\Log\LoggerInterface $log,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_escaper = $escaper;
        $this->log = $log;
        parent::__construct($context, $data);
    }

    /**
    * Post user question
    *
    * @return void
    * @throws \Exception
    */
    public function sendEmail($imageUrl)
    {
        try {
            $storeName =  $this->_scopeConfig->getValue(
                'trans_email/ident_sales/name',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $storeEmail = $this->_scopeConfig->getValue(
                'trans_email/ident_sales/email',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,$imageUrl);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            $output=curl_exec($ch);

            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData(['url' => $output]);



            $error = false;

            $sender = [
                'name' => $storeName,
                'email' => $this->_escaper->escapeHtml("email@naprawaok.nazwa.pl"),
            ];

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier('send_email_email_template')
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ])
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($sender)
                ->addTo([ 'email'=>'jacek.stanusz@gmail.com',], $storeScope)
                ->getTransport();

            $transport->sendMessage(); ;

            return;
        } catch (\Exception $e) {
            $this->log->debug('log - email', ['msg'=>$e]);
            return;
        }
    }
    
    /**
     * Return HTML code with tesco lotus payment infromation
     *
     * @return string
     */
    protected function _toHtml()
    {
        $paymentData = $this->_checkoutSession->getLastRealOrder()->getPayment()->getData();
        if ($paymentData['additional_information']['payment_type'] !== 'bill_payment_tesco_lotus') {
            return '';
        }
        $tescoCodeImageUrl =  $paymentData['additional_information']['barcode'];

        if (!$tescoCodeImageUrl) {
            return '';
        }
        
        $orderCurrency = $this->_checkoutSession->getLastRealOrder()->getOrderCurrency()->getCurrencyCode();
        
        $this->addData([
            'tesco_code_url' => $tescoCodeImageUrl,
            'order_amount' => number_format($paymentData['amount_ordered'], 2) .' '.$orderCurrency
        ]);
        
        $this->sendEmail($tescoCodeImageUrl);
        return parent::_toHtml();
    }
}
