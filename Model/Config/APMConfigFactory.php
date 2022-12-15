<?php

namespace Omise\Payment\Model\Config;

use Omise\Payment\Model\Config\Alipay;
use Omise\Payment\Model\Config\Conveniencestore;
use Omise\Payment\Model\Config\Fpx;
use Omise\Payment\Model\Config\Pointsciti;
use Omise\Payment\Model\Config\Internetbanking;
use Omise\Payment\Model\Config\Installment;
use Omise\Payment\Model\Config\Tesco;
use Omise\Payment\Model\Config\Paynow;
use Omise\Payment\Model\Config\Promptpay;
use Omise\Payment\Model\Config\Truemoney;
use Omise\Payment\Model\Config\Alipayplus;
use Omise\Payment\Model\Config\Mobilebanking;
use Omise\Payment\Model\Config\Rabbitlinepay;
use Omise\Payment\Model\Config\Ocbcpao;
use Omise\Payment\Model\Config\Grabpay;
use Omise\Payment\Model\Config\Boost;
use Omise\Payment\Model\Config\DuitnowOBW;
use Omise\Payment\Model\Config\DuitnowQR;
use Omise\Payment\Model\Config\MaybankQR;
use Omise\Payment\Model\Config\Shopeepay;
use Omise\Payment\Model\Config\Touchngo;

class APMConfigFactory
{
    public function __construct(MagentoScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager)
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function getAPM($apmCode)
    {
        switch ($method->getMethod()) {
            case Alipay::CODE:
                return new Alipay($this->scopeConfig, $this->storeManager);
            case Tesco::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'bill_payment_tesco_lotus'
                ];
                break;
            case Internetbanking::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => $method->getAdditionalInformation(InternetbankingDataAssignObserver::OFFSITE)
                ];
                break;
            case Installment::CODE:
                $installmentId = $method->getAdditionalInformation(InstallmentDataAssignObserver::OFFSITE);
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE              => $installmentId,
                    self::SOURCE_INSTALLMENT_TERMS => $method->getAdditionalInformation(
                        InstallmentDataAssignObserver::TERMS
                    ),
                    self::ZERO_INTEREST_INSTALLMENTS => ('installment_mbb' === $installmentId)
                ];
                break;
            case Truemoney::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE         => 'truemoney',
                    self::SOURCE_PHONE_NUMBER => $method->getAdditionalInformation(
                        TruemoneyDataAssignObserver::PHONE_NUMBER
                    ),
                ];
                break;
            case Conveniencestore::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE         => 'econtext',
                    self::SOURCE_PHONE_NUMBER => $method->getAdditionalInformation(
                        ConveniencestoreDataAssignObserver::PHONE_NUMBER
                    ),
                    self::SOURCE_EMAIL        => $method->getAdditionalInformation(
                        ConveniencestoreDataAssignObserver::EMAIL
                    ),
                    self::SOURCE_NAME         => $method->getAdditionalInformation(
                        ConveniencestoreDataAssignObserver::CUSTOMER_NAME
                    )
                ];
                break;
            case Paynow::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'paynow'
                ];
                break;
            case Promptpay::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'promptpay'
                ];
                break;
            case Pointsciti::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'points_citi'
                ];
                break;
            case Fpx::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'fpx',
                    self::BANK => $method->getAdditionalInformation(
                        FpxDataAssignObserver::BANK
                    )
                ];
                break;
            case Alipayplus::ALIPAY_CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE   => 'alipay_cn',
                    self::PLATFORM_TYPE => $this->helper->getPlatformType(),
                ];
                break;
            case Alipayplus::ALIPAYHK_CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE   => 'alipay_hk',
                    self::PLATFORM_TYPE => $this->helper->getPlatformType(),
                ];
                break;
            case Alipayplus::DANA_CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE   => 'dana',
                    self::PLATFORM_TYPE => $this->helper->getPlatformType(),
                ];
                break;
            case Alipayplus::GCASH_CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE   => 'gcash',
                    self::PLATFORM_TYPE => $this->helper->getPlatformType(),
                ];
                break;
            case Alipayplus::KAKAOPAY_CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE   => 'kakaopay',
                    self::PLATFORM_TYPE => $this->helper->getPlatformType(),
                ];
                break;
            case Touchngo::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE   => 'touch_n_go',
                    self::PLATFORM_TYPE => $this->helper->getPlatformType(),
                ];
                break;
            case Mobilebanking::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => $method->getAdditionalInformation(MobilebankingDataAssignObserver::OFFSITE),
                    self::PLATFORM_TYPE => $this->helper->getPlatformType()
                ];
                break;
            case Rabbitlinepay::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'rabbit_linepay'
                ];
                break;
            case Ocbcpao::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'mobile_banking_ocbc_pao',
                    self::PLATFORM_TYPE => $this->helper->getPlatformType(),
                ];
                break;
            case Grabpay::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'grabpay',
                    self::PLATFORM_TYPE => $this->helper->getPlatformType(),
                ];
                break;
            case Boost::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'boost',
                ];
                break;
            case DuitnowOBW::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'duitnow_obw',
                    self::BANK => $method->getAdditionalInformation(
                        DuitnowOBWDataAssignObserver::BANK
                    )
                ];
                break;
            case DuitnowQR::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'duitnow_qr',
                ];
                break;
            case MaybankQR::CODE:
                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => 'maybank_qr',
                ];
                break;
            case Shopeepay::CODE:
                $defaultSourceType = 'shopeepay';
                $jumpAppSourceType = 'shopeepay_jumpapp';
                $isJumpAppEnabled = $this->capabilities->isBackendEnabled($jumpAppSourceType);
                $this->logger->debug('isJumpAppEnabled: ');
                $this->logger->debug(print_r($isJumpAppEnabled, true));

                if ($this->helper->isMobilePlatform() && $isJumpAppEnabled) {
                    $defaultSourceType = $jumpAppSourceType;
                }

                $this->logger->debug('defaultSourceType: ' . $defaultSourceType);

                $paymentInfo[self::SOURCE] = [
                    self::SOURCE_TYPE => $defaultSourceType
                ];
                break;
        }
    }
}