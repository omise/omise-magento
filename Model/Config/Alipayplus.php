<?php
namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Config;

class Alipayplus extends Config
{
    /**
     * @var string
     */
    const ALIPAY_CODE = 'omise_offsite_alipaycn';
    const ALIPAYHK_CODE = 'omise_offsite_alipayhk';
    const DANA_CODE = 'omise_offsite_dana';
    const GCASH_CODE = 'omise_offsite_gcash';
    const KAKAOPAY_CODE = 'omise_offsite_kakaopay';
    const TOUCHNGO_CODE = 'omise_offsite_touchngo';

     /**
     * @var string
     */
    const ALIPAY_NAME = 'alipay_cn';
    const ALIPAYHK_NAME = 'alipay_hk';
    const DANA_NAME = 'dana';
    const GCASH_NAME = 'gcash';
    const KAKAOPAY_NAME = 'kakaopay';
    const TOUCHNGO_NAME = 'touch_n_go';
}
