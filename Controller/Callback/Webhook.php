<?php
namespace Omise\Payment\Controller\Callback;

use Exception;
use Omise\Payment\Model\Config\Cc as Config;

class Webhook extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var \Omise\Payment\Model\Config\Cc
     */
    protected $config;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $session,
        Config  $config
    ) {
        parent::__construct($context);

        $this->session = $session;
        $this->config  = $config;
    }

    /**
     * @return void
     */
    public function execute()
    {
        
    }
}
