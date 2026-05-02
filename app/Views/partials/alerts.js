(() => {
    const alertPayloads = document.querySelectorAll('script[data-alert-payload]');
    const alertTitles = {
        success: 'Success',
        warning: 'Warning',
        error: 'Error',
    };

    const parseAlerts = (payloadElement) => {
        try {
            const alerts = JSON.parse(payloadElement.textContent || '[]');
            return Array.isArray(alerts) ? alerts : [];
        } catch (error) {
            return [];
        }
    };

    const formatAlertMessage = (alertConfig) => {
        const type = typeof alertConfig.type === 'string' ? alertConfig.type : 'error';
        const title = alertTitles[type] || 'Notice';
        const message = typeof alertConfig.message === 'string' ? alertConfig.message.trim() : '';
        const items = Array.isArray(alertConfig.items)
            ? alertConfig.items
                .map((item) => String(item).trim())
                .filter((item) => item !== '')
            : [];

        const lines = [title];

        if (message !== '') {
            lines.push('', message);
        }

        if (items.length > 0) {
            lines.push('', ...items.map((item) => `- ${item}`));
        }

        return lines.join('\n').trim();
    };

    alertPayloads.forEach((payloadElement) => {
        if (!(payloadElement instanceof HTMLScriptElement) || payloadElement.dataset.alertsRendered === 'true') {
            return;
        }

        parseAlerts(payloadElement).forEach((alertConfig) => {
            const alertMessage = formatAlertMessage(alertConfig);

            if (alertMessage !== '') {
                window.alert(alertMessage);
            }
        });

        payloadElement.dataset.alertsRendered = 'true';
    });
})();
