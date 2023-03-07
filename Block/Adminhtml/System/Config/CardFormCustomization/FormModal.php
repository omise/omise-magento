<?php

namespace Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http;

class FormModal extends Field
{
    public $request;
    public $storeManager;

    /**
     * URL for omise webhook.
     */
    const URI = 'omise/callback/webhook';

    /**
     * @var \Magento\Framework\Url
     */
    protected $urlHelper;

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
        return file_get_contents(__DIR__ . '/css/style.css');
    }

    /**
     * get javascript
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function getScript(AbstractElement $element)
    {
        $id = $element->getId();
        $value = $element->getData('value');

        $script = sprintf('window.OMISE_CARD_CUSTOMIZATION_INPUT_ID = `%s`;', $id);
        $script .= sprintf('window.OMISE_CARD_CUSTOMIZATION_DESIGN = `%s`;', $value);
        $script .= sprintf('window.OMISE_CARD_CUSTOMIZATION_DARK_THEME = `%s`;', json_encode(Theme::getDarkTheme()));
        $script .= sprintf('window.OMISE_CARD_CUSTOMIZATION_LIGHT_THEME = `%s`;', json_encode(Theme::getLightTheme()));
        $script .= file_get_contents(__DIR__ . '/js/script.js');

        return $script;
    }

    /**
     * get HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function getHtml(AbstractElement $element)
    {
        $value = $element->getData('value');
        $id = $element->getId();
        $name = $element->getName();

        $html = '<input id="' . $id . '" name="' . $name . '" value="' . $value . '" type="hidden">';
        $html .= file_get_contents(__DIR__ . '/form-modal.html');
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

    public function getDefaultDesign() {

    }
}
