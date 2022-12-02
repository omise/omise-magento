<?php

namespace Omise\Payment\Block\Checkout\Payment;

class StoreCurrency extends \Magento\Framework\View\Element\Template
{
	protected $_storeManager;
	protected $_currency;
	
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,		
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Directory\Model\Currency $currency,		
		array $data = []
	)
	{		
		$this->_storeManager = $storeManager;
		$this->_currency = $currency;		
		parent::__construct($context, $data);
	}
	
		
	/**
     * Get store base currency code
     *
     * @return string
     */
	public function getBaseCurrencyCode()
	{
		return $this->_storeManager->getStore()->getBaseCurrencyCode();
	}
	
	/**
     * Get current store currency code
     *
     * @return string
     */
	public function getCurrentCurrencyCode()
	{
		return $this->_storeManager->getStore()->getCurrentCurrencyCode();
	}	
	
	/**
     * Get default store currency code
     *
     * @return string
     */
	public function getDefaultCurrencyCode()
	{
		return $this->_storeManager->getStore()->getDefaultCurrencyCode();
	}
	
	/**
     * Get allowed store currency codes
     *
     * If base currency is not allowed in current website config scope,
     * then it can be disabled with $skipBaseNotAllowed
     *
     * @param bool $skipBaseNotAllowed
     * @return array
     */
	public function getAvailableCurrencyCodes($skipBaseNotAllowed = false)
	{
		return $this->_storeManager->getStore()->getAvailableCurrencyCodes($skipBaseNotAllowed);
	}
	
	/**
     * Get array of installed currencies for the scope
     *
     * @return array
     */
	public function getAllowedCurrencies()
	{
		return $this->_storeManager->getStore()->getAllowedCurrencies();
	}
	
	/**
     * Get current currency rate
     *
     * @return float
     */
	public function getCurrentCurrencyRate()
	{
		return $this->_storeManager->getStore()->getCurrentCurrencyRate();
	}
	
	/**
     * Get currency symbol for current locale and currency code
     *
     * @return string
     */	
	public function getCurrentCurrencySymbol()
	{
		return $this->_currency->getCurrencySymbol();
	}	
}