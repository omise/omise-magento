<?php

namespace Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Filesystem\Driver\File;

class FormModal extends Field
{
    public $request;
    public $storeManager;
    public $localFileSystem;
    public $theme;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Request\Http $http
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Http $request,
        array   $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->localFileSystem = new File();
        $this->theme = new Theme();
        parent::__construct($context, $data);
    }

    /**
     * get css
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function getCss(AbstractElement $element)
    {
        return $this->localFileSystem->fileGetContents(__DIR__ . '/css/style.css');
    }

    /**
     * get javascript
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function getScript(AbstractElement $element)
    {
        $script = $this->addGlobalJsVariables([
            'OMISE_CC_INPUT_ID' => $element->getId(),
            'OMISE_CC_DESIGN' => $element->getData('value'),
            'OMISE_CC_LIGHT_THEME' => json_encode($this->theme->getLightTheme()),
            'OMISE_CC_DARK_THEME' => json_encode($this->theme->getDarkTheme()),
            'OMISE_CC_INPUT_INHERIT_VALUE' => $element->getInherit(),
            'OMISE_CC_INPUT_INHERIT_LABEL' => $this->_getInheritCheckboxLabel($element),
            'OMISE_CC_INPUT_INHERIT_SHOULD_SHOW' => $this->_isInheritCheckboxRequired($element),
        ]);

        $script .= $this->localFileSystem->fileGetContents(__DIR__ . '/js/script.js');
        return $script;
    }

    /**
     * add global javascript variables
     * 
     * @param Array
     */
    private function addGlobalJsVariables($array)
    {
        $script = '';
        foreach ($array as $key => $value) {
            $script .= sprintf('window.%s = `%s`;', $key, $value);
        }
        return $script;
    }

    /**
     * get inherit input name (use website checkbox)
     * 
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     */
    protected function getInheritCheckboxName($element)
    {
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());
        return $namePrefix . '[inherit]';
    }

    /**
     * get HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function getHtml(AbstractElement $element)
    {
        $isCheckboxRequired = $this->_isInheritCheckboxRequired($element);
        $value = $element->getData('value');
        $id = $element->getId();
        $name = $element->getName();
        $html = sprintf(
            "<input id='%s' name='%s' value='%s' type='hidden'>",
            $id,
            $name,
            $value
        );
        if ($isCheckboxRequired) {
            $html .= sprintf(
                "<input id='%s' name='%s' value='%s' type='hidden'>",
                $id . '_inherit',
                $this->getInheritCheckboxName($element),
                $element->getInherit()
            );
        }
        $html .= $this->localFileSystem->fileGetContents(__DIR__ . '/form-modal.html');
        return $html;
    }

    /**
     * render HTML markup for given element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = '<style>' . $this->getCss($element) . '</style>';
        $html .= $this->getHtml($element);
        $html .= '<script>' . $this->getScript($element) . '</script>';
        return $html;
    }
}
