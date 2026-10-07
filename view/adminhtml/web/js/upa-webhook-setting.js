define([], function () {
    'use strict';

    return function () {
        var upaFlag = document.querySelector('[name="groups[omise][fields][is_upa_feature_flag_enabled][value]"]');
        if (!upaFlag || upaFlag.dataset.webhookToggleBound) {
            return;
        }

        upaFlag.dataset.webhookToggleBound = '1';

        var dynamicWebhooksName = 'groups[omise][fields][dynamic_webhooks][value]';

        function updateWebhookFields() {
            var enabled = upaFlag.value === '1';

            var dynamicWebhooks = document.querySelector('[name="' + dynamicWebhooksName + '"]');
            if (!dynamicWebhooks || dynamicWebhooks.type === 'hidden') {
                return;
            }

            var hidden = document.querySelector('input[type="hidden"][name="' + dynamicWebhooksName + '"]');
            if (enabled) {
                dynamicWebhooks.value = '1';
                dynamicWebhooks.disabled = true;
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = dynamicWebhooksName;
                    hidden.value = '1';
                    dynamicWebhooks.parentNode.appendChild(hidden);
                }
            } else {
                dynamicWebhooks.disabled = false;
                if (hidden) {
                    hidden.remove();
                }
            }
        }

        upaFlag.addEventListener('change', updateWebhookFields);
        updateWebhookFields();
    };
});
