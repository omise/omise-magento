<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Payment\Model\InfoInterface;
use Omise\Payment\Model\Customer;

class CreditCardDataObserver extends AbstractDataAssignObserver
{
    /**
     * @var string
     */
    const TOKEN         = 'omise_card_token';
    const CARD          = 'omise_card';
    const REMEMBER_CARD = 'omise_save_card';
    const CUSTOMER      = 'customer';
    const CHARGE_ID     = 'charge_id';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::TOKEN,
        self::CARD,
        self::REMEMBER_CARD,
        self::CUSTOMER,
        self::CHARGE_ID
    ];

    /**
     * @var Omise\Payment\Model\Customer
     */
    protected $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Handle 'payment_method_assign_data_omise_cc' event.
     *
     * @param  \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @see    etc/events.xml
     * @see    Magento\Payment\Model\Method\AbstractMethod::assignData()
     */
    public function execute(Observer $observer)
    {
        $dataObject     = $this->readDataArgument($observer);
        $additionalData = $dataObject->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (! is_array($additionalData)) {
            return;
        }

        $this->maybeUseExistingCard($additionalData);
        $this->maybeSaveCustomerCard($additionalData);
        $this->setPaymentAdditionalInformation($this->readPaymentModelArgument($observer), $additionalData);
    }

    /**
     * Deciding whether the 'omise_card' is presented or not.
     *
     * @param  array &$additionalData
     *
     * @return void
     */
    protected function maybeUseExistingCard(array &$additionalData)
    {
        if (! empty($additionalData[self::CARD])) {
            $additionalData[self::CUSTOMER] = $this->customer->getId();
        }
    }

    /**
     * Deciding whether the card-token needed to be saved or not.
     *
     * @param  array &$additionalData
     *
     * @return void
     */
    protected function maybeSaveCustomerCard(array &$additionalData)
    {
        if (! empty($additionalData[self::REMEMBER_CARD])) {
            $customer = $this->customer->addCard($additionalData[self::TOKEN]);
            $card     = $customer->getLatestCard();

            $additionalData[self::CUSTOMER] = $customer->getId();
            $additionalData[self::CARD]     = $card['id'];
        }
    }

    /**
     * @param  \Magento\Payment\Model\InfoInterface $paymentInfo
     * @param  array                                $additionalData
     *
     * @return void
     */
    protected function setPaymentAdditionalInformation(InfoInterface $paymentInfo, array $additionalData)
    {
        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}
