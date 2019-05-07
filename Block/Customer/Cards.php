<?php
namespace Omise\Payment\Block\Customer;

use Magento\Framework\View\Element\Template;
use Omise\Payment\Model\Customer;

/**
 * Class Cards
 */
class Cards extends Template
{
    /**
     * @var Omise\Payment\Model\Customer;
     */
    private $customer;

    /**
     * @param \Magento\Framework\View\Element\Template $context
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
     * @return  array|null
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
        return $this->getUrl('omise/cards/deleteaction', ['card_id' => $card['id']]);
    }
}
