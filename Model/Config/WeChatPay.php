<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class WeChatPay extends Config
{
    /**
     * @var string
     */
    const CODE = 'omise_offsite_wechat_pay';

    /**
     * Backends identifier
     * @var string
     */
    const ID = 'wechat_pay';
}
