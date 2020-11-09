<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class SalesRuleAddConditionObserver extends AbstractDataAssignObserver
{
    protected $omiseCardCondition;

    public function __construct(
        \Omise\PAyment\Model\Rule\Condition\OmiseCardCondition $omiseCardCondition
    )
    {
        $this->omiseCardCondition = $omiseCardCondition;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        $additional = $observer->getAdditional();
        $conditions = (array) $additional->getConditions();
 
        $conditions = array_merge_recursive($conditions,
        [
            [
                'label' => __('Omise Attributes'), 'value' =>
                $this->getOmiseCardCondition()
            ]
        ]);

        $additional->setConditions($conditions);
        return $this;
    }

    private function getOmiseCardCondition()
    {
        $conditionAttributes = $this->omiseCardCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($conditionAttributes as $code => $label) {
            $attributes[] = [
                'value' => \Omise\Payment\Model\Rule\Condition\OmiseCardCondition::class,
                //'value' => 'Omise\Payment\Model\Rule\Condition\OmiseCardCondition|' . $code,
                'label' => $label,
            ];
        }
        return $attributes;
    }
}
