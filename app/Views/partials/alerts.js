(() => {
    const alertPayloads = document.querySelectorAll('script[data-alert-payload]');
    const alertTitles = {
        success: 'Success',
        warning: 'Warning',
        error: 'Error',
    };
    const modalTypeClasses = ['app-alert-modal--success', 'app-alert-modal--warning', 'app-alert-modal--error'];
    const modalBodyClass = 'app-body--alert-open';

    const parseAlerts = (payloadElement) => {
        try {
            const alerts = JSON.parse(payloadElement.textContent || '[]');
            return Array.isArray(alerts) ? alerts : [];
        } catch (error) {
            return [];
        }
    };

    const normalizeAlert = (alertConfig) => {
        const type = typeof alertConfig.type === 'string' ? alertConfig.type : 'error';
        const title = alertTitles[type] || 'Notice';
        const message = typeof alertConfig.message === 'string' ? alertConfig.message.trim() : '';
        const items = Array.isArray(alertConfig.items)
            ? alertConfig.items
                .map((item) => String(item).trim())
                .filter((item) => item !== '')
            : [];

        if (message === '' && items.length === 0) {
            return null;
        }

        return {
            type,
            title,
            message,
            items,
        };
    };

    const collectAlerts = () => {
        const alerts = [];

        alertPayloads.forEach((payloadElement) => {
            if (!(payloadElement instanceof HTMLScriptElement) || payloadElement.dataset.alertsRendered === 'true') {
                return;
            }

            parseAlerts(payloadElement).forEach((alertConfig) => {
                const normalizedAlert = normalizeAlert(alertConfig);

                if (normalizedAlert !== null) {
                    alerts.push(normalizedAlert);
                }
            });

            payloadElement.dataset.alertsRendered = 'true';
        });

        return alerts;
    };

    const getModal = () => {
        const existingModal = document.getElementById('app-alert-modal');

        if (existingModal instanceof HTMLDivElement) {
            return existingModal;
        }

        const modal = document.createElement('div');
        modal.id = 'app-alert-modal';
        modal.className = 'app-alert-modal';
        modal.hidden = true;
        modal.innerHTML = `
            <button type="button" class="app-alert-modal__backdrop" data-alert-dismiss aria-label="Close alert"></button>
            <section class="app-alert-modal__dialog" role="alertdialog" aria-modal="true" aria-labelledby="app-alert-title" aria-describedby="app-alert-message" tabindex="-1">
                <div class="app-alert-modal__accent"></div>
                <div class="app-alert-modal__copy">
                    <span class="app-alert-modal__badge" data-alert-badge>Notice</span>
                    <h2 id="app-alert-title" class="app-alert-modal__title">Notice</h2>
                    <p id="app-alert-message" class="app-alert-modal__message" hidden></p>
                    <ul class="app-alert-modal__list" data-alert-items hidden></ul>
                </div>
                <div class="app-alert-modal__actions">
                    <button type="button" class="btn btn-primary app-alert-modal__button" data-alert-confirm>OK</button>
                </div>
            </section>
        `;

        document.body.appendChild(modal);

        return modal;
    };

    const showAlerts = (alerts) => {
        if (alerts.length === 0) {
            return;
        }

        const modal = getModal();
        const dialog = modal.querySelector('.app-alert-modal__dialog');
        const badge = modal.querySelector('[data-alert-badge]');
        const title = modal.querySelector('.app-alert-modal__title');
        const message = modal.querySelector('.app-alert-modal__message');
        const items = modal.querySelector('[data-alert-items]');
        const confirmButton = modal.querySelector('[data-alert-confirm]');
        const dismissButton = modal.querySelector('[data-alert-dismiss]');

        if (
            !(dialog instanceof HTMLElement) ||
            !(badge instanceof HTMLElement) ||
            !(title instanceof HTMLElement) ||
            !(message instanceof HTMLElement) ||
            !(items instanceof HTMLUListElement) ||
            !(confirmButton instanceof HTMLButtonElement) ||
            !(dismissButton instanceof HTMLButtonElement)
        ) {
            return;
        }

        let alertIndex = 0;
        const previousFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;

        const closeModal = () => {
            modal.hidden = true;
            modal.classList.remove(...modalTypeClasses);
            document.body.classList.remove(modalBodyClass);
            document.removeEventListener('keydown', handleKeydown, true);

            if (previousFocus instanceof HTMLElement) {
                previousFocus.focus();
            }
        };

        const renderAlert = () => {
            const alertConfig = alerts[alertIndex];

            if (!alertConfig) {
                closeModal();
                return;
            }

            modal.classList.remove(...modalTypeClasses);
            modal.classList.add(`app-alert-modal--${alertConfig.type}`);

            badge.textContent = alertConfig.title;
            title.textContent = alertConfig.message !== '' ? alertConfig.message : `${alertConfig.title} notice`;

            if (alertConfig.items.length > 0) {
                items.replaceChildren(...alertConfig.items.map((item) => {
                    const listItem = document.createElement('li');
                    listItem.textContent = item;

                    return listItem;
                }));
                items.hidden = false;
            } else {
                items.replaceChildren();
                items.hidden = true;
            }

            const supportingMessage = alertConfig.items.length > 0
                ? 'Review the details below before continuing.'
                : '';

            if (supportingMessage !== '') {
                message.textContent = supportingMessage;
                message.hidden = false;
            } else {
                message.textContent = '';
                message.hidden = true;
            }

            modal.hidden = false;
            document.body.classList.add(modalBodyClass);
            window.requestAnimationFrame(() => {
                confirmButton.focus();
            });
        };

        const advanceAlert = () => {
            alertIndex += 1;

            if (alertIndex >= alerts.length) {
                closeModal();
                return;
            }

            renderAlert();
        };

        const handleKeydown = (event) => {
            if (modal.hidden) {
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                advanceAlert();
                return;
            }

            if (event.key === 'Tab') {
                event.preventDefault();
                confirmButton.focus();
            }
        };

        confirmButton.onclick = advanceAlert;
        dismissButton.onclick = advanceAlert;
        document.addEventListener('keydown', handleKeydown, true);
        renderAlert();
    };

    const showAlertsAfterPaint = () => {
        window.requestAnimationFrame(() => {
            window.setTimeout(() => {
                showAlerts(collectAlerts());
            }, 0);
        });
    };

    if (document.readyState === 'complete') {
        showAlertsAfterPaint();
        return;
    }

    window.addEventListener('load', showAlertsAfterPaint, { once: true });
})();
