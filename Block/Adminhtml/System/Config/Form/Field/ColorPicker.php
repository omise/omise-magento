<?php

namespace Omise\Payment\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ColorPicker extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);
        $id = $element->getHtmlId();
        $value = $element->getValue() ?: '#1979C3';
        $html .= <<<HTML
        <style>
            #$id-wrapper{
                position:relative;
                display:inline-block;
                width:100%;
                max-width:600px;
            }
            #$id{
                padding-right:42px;
            }
            #$id-preview{
                position:absolute;
                top:5px;
                right:6px;
                width:24px;
                height:24px;
                border:1px solid #adadad;
                border-radius:2px;
                cursor:pointer;
                background:{$value};
                box-sizing:border-box;
            }
            #$id-picker{
                position:absolute;
                visibility:hidden;
                width:0;
                height:0;
                opacity:0;
            }
        </style>
        <script>
            require(['jquery'], function ($) {
                var input = $('#$id');
                input.wrap('<div id="$id-wrapper"></div>');
                input.after(
                    '<div id="$id-preview"></div>' +
                    '<input type="color" id="$id-picker" value="{$value}">'
                );
                var preview = $('#$id-preview');
                var picker = $('#$id-picker');
                preview.on('click', function () {
                    picker.trigger('click');
                });
                picker.on('input change', function () {
                    input.val(this.value);
                    preview.css('background', this.value);
                });
                input.on('keyup change', function () {
                    var value = $(this).val();
                    if(/^#[0-9A-Fa-f]{6}$/.test(value)){
                        picker.val(value);
                        preview.css('background', value);
                    }
                });
            });
        </script>
        HTML;
        return $html;
    }
}