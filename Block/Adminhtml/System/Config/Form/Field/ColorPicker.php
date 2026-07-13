<?php

namespace Omise\Payment\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ColorPicker extends Field
{
    /**
     * Render field.
     */
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
            padding-right:40px;
        }
        #$id-preview{
            position:absolute;
            right:8px;
            top:5px;
            width:24px;
            height:24px;
            border:1px solid #adadad;
            border-radius:2px;
            cursor:pointer;
            background:$value;
            box-sizing:border-box;
        }
        #$id-picker{
            position:absolute;
            width:0;
            height:0;
            opacity:0;
            visibility:hidden;
        }
        </style>
        <script>
        require([
            'jquery',
            'mage/validation'
        ], function ($) {
            $.validator.addMethod(
                'validate-hex-color',
                function (value) {
                    value = $.trim(value);
                    if (value === '') {
                        return true;
                    }
                    return /^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(value);
                },
                $.mage.__('Please enter a valid HEX color (Example: #1979C3).')
            );

            var input = $('#$id');
            input.attr(
                'data-validate',
                '{"validate-hex-color":true}'
            );
            input.wrap('<div id="$id-wrapper"></div>');
            input.after(
                '<div id="$id-preview"></div>' +
                '<input type="color" id="$id-picker" value="$value">'
            );
            var picker = $('#$id-picker');
            var preview = $('#$id-preview');

            preview.on('click', function () {
                picker.trigger('click');
            });
            picker.on('input change', function () {
                input.val(this.value);
                preview.css(
                    'background',
                    this.value
                );
                input.valid();
            });
            input.on('keyup change', function () {
                var value = $.trim($(this).val());
                if (/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(value)) {
                    picker.val(value);
                    preview.css(
                        'background',
                        value
                    );
                }
                input.valid();
            });
        });
        </script>
        HTML;
        return $html;
    }
}