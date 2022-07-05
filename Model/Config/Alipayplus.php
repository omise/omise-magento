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
     * Backends identifier
     * @var string
     */
    const ALIPAY_ID = 'alipay_cn';
    const ALIPAYHK_ID = 'alipay_hk';
    const DANA_ID = 'dana';
    const GCASH_ID = 'gcash';
    const KAKAOPAY_ID = 'kakaopay';
    const TOUCHNGO_ID = 'touch_n_go';
}
