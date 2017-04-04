<?php
namespace Omise\Payment\Controller\Callback;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Internetbanking extends Action
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        echo "here";
        exit;
    }
}
