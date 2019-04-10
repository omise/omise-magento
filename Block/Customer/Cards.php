<?php
namespace Omise\Payment\Block\Customer;

use Magento\Framework\View\Element\Template;
use Omise\Payment\Model\Customer;

/**
 * Class PaymentTokens
 */
class Cards extends Template
{
    /**
     * @var Omise\Payment\Model\Customer;
     */
    private $customer;

    /**
     * @param Template\Context $context
     * @param Omise\Payment\Model\Customer $customer
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Customer $customer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customer = $customer;
    }

    /**
     * @return  array
     */
    public function getCards()
    {
        return !$this->customer->getMagentoCustomerId() || !$this->customer->getId() ? null : $this->customer->cards(['order' => 'reverse_chronological']);
    }

    /**
     * @return  string
     */
    public function getDeleteLink($card)
    {
        return $this->getUrl('omise/methods/delete', ['card_id' => $card['id']]);
    }
}