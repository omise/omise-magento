define([], function () {
    'use strict';

    return function () {
        var upaFlag = document.querySelector('[name="groups[omise][fields][is_upa_feature_flag_enabled][value]"]');
        if (!upaFlag || upaFlag.dataset.webhookToggleBound) {
            return;
        }

        upaFlag.dataset.webhookToggleBound = '1';

        var webhookFieldNames = [
            'groups[omise][fields][webhook_status][value]',
            'groups[omise][fields][dynamic_webhooks][value]'
        ];

        function updateWebhookFields() {
            var enabled = upaFlag.value === '1';

            webhookFieldNames.forEach(function (fieldName) {
                var field = document.querySelector('[name="' + fieldName + '"]');
                if (!field || field.type === 'hidden') {
                    return;
                }

                var hidden = document.querySelector('input[type="hidden"][name="' + fieldName + '"]');
                if (enabled) {
                    field.value = '1';
                    field.disabled = true;
                    if (!hidden) {
                        hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = fieldName;
                        hidden.value = '1';
                        field.parentNode.appendChild(hidden);
                    }
                } else {
                    field.disabled = false;
                    if (hidden) {
                        hidden.remove();
                    }
                }
            });
        }

        upaFlag.addEventListener('change', updateWebhookFields);
        updateWebhookFields();
    };
});
