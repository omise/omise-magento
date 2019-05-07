<?php
namespace Omise\Payment\Model;

use Magento\Customer\Model\ResourceModel\Customer as MagentoCustomerResource;
use Magento\Customer\Model\Session                as MagentoCustomerSession;
use Omise\Payment\Model\Api\Customer              as OmiseCustomerAPI;
use Omise\Payment\Model\Omise;

class Customer
{
    /**
     * @var string
     */
    const OMISE_CUSTOMER_ID_FIELD = 'omise_customer_id';

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $magentoCustomer;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $magentoCustomerResource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $magentoCustomerSession;

    /**
     * @var \Omise\Payment\Model\Omise
     */
    protected $omise;

    /**
     * @var \Omise\Payment\Model\Api\Customer
     */
    protected $customerAPI;

    public function __construct(
        MagentoCustomerResource $magentoCustomerResource,
        MagentoCustomerSession  $magentoCustomerSession,
        Omise                   $omise,
        OmiseCustomerAPI        $customerAPI
    ) {
        $this->magentoCustomerResource = $magentoCustomerResource;
        $this->magentoCustomerSession  = $magentoCustomerSession;
        $this->magentoCustomer         = $this->magentoCustomerSession->getCustomer();
        $this->omise                   = $omise;
        $this->customerAPI             = $customerAPI;

        $this->omise->defineUserAgent();
        $this->omise->defineApiVersion();
        $this->omise->defineApiKeys();

        $this->initializeObject();
    }

    /**
     * Check whether the executor is logged in or not.
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->getMagentoCustomerId() ? true : false;
    }

    /**
     * Get an Omise customer id that belongs to
     * a specific user who execute the method.
     *
     * @return string
     */
    public function getId()
    {
        return $this->magentoCustomer->getData(self::OMISE_CUSTOMER_ID_FIELD);
    }

    /**
     * Get a Magento customer id that belongs to
     * a specific user who execute the method.
     *
     * @return int|null
     *
     * @see    magento/module-customer/Model/Session.php
     */
    public function getMagentoCustomerId()
    {
        return $this->magentoCustomerSession->getCustomerId();
    }

    /**
     * Create an Omise customer, then assigning its Omise customer id
     * back to the customer's attribute who execute the method.
     *
     * @return self
     */
    public function create(array $params)
    {
        $this->customerAPI = $this->customerAPI->create($params);

        $this->magentoCustomer->setData(self::OMISE_CUSTOMER_ID_FIELD, $this->customerAPI->id);
        $this->magentoCustomerResource->saveAttribute($this->magentoCustomer, self::OMISE_CUSTOMER_ID_FIELD);

        return $this;
    }

    /**
     * Attach an Omise Token to a Customer object.
     *
     * @param string $cardToken
     *
     * @return self
     */
    public function addCard($cardToken)
    {
        if (! $this->getId()) {
            $this->create([
                'email'       => $this->magentoCustomer->getEmail(),
                'description' => trim($this->magentoCustomer->getFirstname() . ' ' . $this->magentoCustomer->getLastname())
            ]);
        }

        $this->customerAPI = $this->customerAPI->update(['card' => $cardToken]);

        return $this;
    }

    /**
     * Delete card
     *
     * @param  string $cardToken
     *
     * @see    https://github.com/omise/omise-php/blob/master/lib/omise/OmiseCardList.php
     */
    public function deleteCard($cardToken)
    {
        $card = $this->customerAPI->cards()->retrieve($cardToken);
        $card->destroy();
    }

    /**
     * Retrieve all cards that belong to a specific Omise Customer object.
     *
     * @param  array $options
     *
     * @return \OmiseCardList
     *
     * @see    https://github.com/omise/omise-php/blob/master/lib/omise/OmiseCardList.php
     */
    public function cards($options = [])
    {
        return $this->customerAPI->cards($options);
    }

    /**
     * @return array  of Omise card object.
     *
     * @see    https://www.omise.co/cards-api
     */
    public function getLatestCard()
    {
        $cards = $this->cards(['order' => 'reverse_chronological']);

        return $cards['total'] > 0 ? $cards['data'][0] : null;
    }

    /**
     * Load and prepare Omise customer object
     * when the class is being executed.
     */
    protected function initializeObject()
    {
        if ($this->getId()) {
            $this->customerAPI = $this->customerAPI->find($this->getId());
        }
    }
}
