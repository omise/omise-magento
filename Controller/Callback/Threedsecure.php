<?php
namespace Omise\Payment\Controller\Callback;

use Magento\Framework\App\Action\Action;

class Threedsecure extends Action
{
    /**
     * @var string
     */
    const PATH_CART    = 'checkout/cart';
    const PATH_SUCCESS = 'checkout/onepage/success';

    /**
     * @return void
     */
    public function execute()
    {
        echo "3-D Secure callback validation";
    }
}
