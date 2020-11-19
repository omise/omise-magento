<?php

/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Omise\Payment\Controller\Adminhtml\Promotion;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * SalesRule save controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */

class Save extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote implements HttpPostActionInterface
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;
    private $log;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param TimezoneInterface $timezone
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Psr\Log\LoggerInterface $log,
        TimezoneInterface $timezone = null,
        DataPersistorInterface $dataPersistor = null
    ) {
        $this->log = $log;
        $this->log->debug("this is from logger");
        parent::__construct($context, $coreRegistry, $fileFactory, $dateFilter);
        $this->timezone =  $timezone ?? \Magento\Framework\App\ObjectManager::getInstance()->get(
            TimezoneInterface::class
        );
        $this->dataPersistor = $dataPersistor ?? \Magento\Framework\App\ObjectManager::getInstance()->get(
            DataPersistorInterface::class
        );
    }

    /**
     * Promo quote save action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!isset($data['promo_code'])) {
            $this->messageManager->addErrorMessage(
                __('Invalid Code. Please contact Omise for more information')
            );
            return $this->_redirect('omise/promotion/new');
        }

        try {
            $data = json_decode(base64_decode($data['promo_code']), true);
            $this->log->debug('requested params', $data);

            if (!$data) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unable to decode promotion code'));
            }
            if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent' && isset($data['discount_amount'])) {
                $data['discount_amount'] = min(100, $data['discount_amount']);
            }
            print_r($data['card_bin'][0]);
            /** @var $model \Magento\SalesRule\Model\Rule */
            $model = $this->_objectManager->create(\Magento\SalesRule\Model\Rule::class);
            $model->setName('Omise promotion - ' . $data['id'])
                ->setDescription('-')
                ->setIsActive(1)
                ->setCustomerGroupIds(array('0', '1', '2', '3'))
                ->setWebsiteIds(array(1))
                ->setFromDate($data['from_date'])
                ->setToDate($data['to_date'])
                ->setSimpleAction($data['simple_action'])
                ->setDiscountAmount($data['discount_amount'])
                ->setStopRulesProcessing(0);

            $item_found = $this->_objectManager->create('Magento\SalesRule\Model\Rule\Condition\Combine')
                ->setValue(1) // 1 == FOUND
                ->setAggregator('any'); // match ALL conditions

            $model->getConditions()->addCondition($item_found);

            foreach ($data['card_bin'] as $bin) {
                $conditions = $this->_objectManager->create('Omise\Payment\Model\Rule\Condition\OmiseCardCondition')
                    ->setType('Omise\Payment\Model\Rule\Condition\OmiseCardCondition')
                    ->setData('attribute', 'card_bin')
                    ->setData('operator', '==')
                    ->setValue($bin);
                $item_found->addCondition($conditions);
            }

            $validateResult = $model->validateData(new \Magento\Framework\DataObject($model->getData()));
            if ($validateResult !== true) {
                foreach ($validateResult as $errorMessage) {
                    echo $errorMessage;
                }
                return;
            }

            $model->save();

            $this->messageManager->addSuccessMessage(__('Cart rule has been saved.'));
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('sales_rule/*/edit', ['id' => $model->getId()]);
                return;
            }
            $this->_redirect('omise/promotion/new');
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('omise/promotion/new');
            return;
        } catch (\Exception $e) {
            print_r($e);
            die();
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the rule data. Please review the error log.')
            );
            $this->_redirect('omise/promotion/new');
            return;
        }
    }

    /**
     * Check if Cart Price Rule with provided id exists.
     *
     * @param \Magento\SalesRule\Model\Rule $model
     * @return bool
     */
    private function checkRuleExists(\Magento\SalesRule\Model\Rule $model): bool
    {
        $id = $this->getRequest()->getParam('rule_id');
        if ($id) {
            $model->load($id);
            if ($model->getId() != $id) {
                return false;
            }
        }
        return true;
    }
}
