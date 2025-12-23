(() => {
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
    const selfMark = 'sf-adtech-' + Math.random().toString(36).slice(2);
    const channel = 'BroadcastChannel' in window ? new BroadcastChannel('sf-adtech') : null;

    const publish = (event) => {
        const payload = { ...event, self: selfMark, at: Date.now() };
        if (channel) {
            channel.postMessage(payload);
        } else {
            localStorage.setItem('sf-adtech-event', JSON.stringify(payload));
        }
    };

    const subscribe = (handler) => {
        if (channel) {
            channel.onmessage = (event) => handler(event.data);
        }

        window.addEventListener('storage', (event) => {
            if (event.key !== 'sf-adtech-event' || !event.newValue) {
                return;
            }
            try {
                const data = JSON.parse(event.newValue);
                handler(data);
            } catch (e) {
                console.error('sync parse error', e);
            }
        });
    };

    const findErrorBox = (form) => form?.querySelector('[data-form-errors]') || document.querySelector('[data-error-box]');
    const findSuccessBox = (form) => form?.querySelector('[data-form-success]') || document.querySelector('[data-flash-box]') || document.querySelector('[data-inline-flash]');

    const showBox = (box, message, type = 'success') => {
        if (!box) return;
        box.classList.remove('d-none', 'alert-danger', 'alert-success', 'alert-info');
        const classMap = {
            success: 'alert-success',
            error: 'alert-danger',
            info: 'alert-info',
        };
        box.classList.add(classMap[type] || 'alert-info');
        box.innerHTML = message;
    };

    const clearBox = (box) => {
        if (!box) return;
        box.classList.add('d-none');
        box.innerHTML = '';
    };

    const buildOfferRow = (offer) => {
        const table = document.querySelector('[data-offers-table]');
        if (!table) return null;
        const showBase = table.dataset.showBase || '/offers';
        const statusBase = table.dataset.statusBase || '/offers';
        const deactivateBase = table.dataset.deactivateBase || '/offers';

        const row = document.createElement('tr');
        row.dataset.entity = 'offer';
        row.dataset.id = offer.id;
        row.innerHTML = `
            <td><a href="${showBase}/${offer.id}">${offer.name}</a></td>
            <td>${offer.price_per_click}</td>
            <td>${offer.target_url}</td>
            <td>
                <form method="POST" action="${statusBase}/${offer.id}/status" class="d-flex gap-2 align-items-center" data-async="true" data-action="offer-status">
                    <input type="hidden" name="_token" value="${csrf}">
                    <select name="status" class="form-select form-select-sm">
                        <option value="draft" ${offer.status === 'draft' ? 'selected' : ''}>draft</option>
                        <option value="active" ${offer.status === 'active' ? 'selected' : ''}>active</option>
                        <option value="inactive" ${offer.status === 'inactive' ? 'selected' : ''}>inactive</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary">Обновить</button>
                </form>
            </td>
            <td>${offer.subscriptions_count}</td>
            <td>
                <div class="d-flex gap-2">
                    <a href="${showBase}/${offer.id}" class="btn btn-sm btn-outline-secondary">Статистика</a>
                    <form method="POST" action="${deactivateBase}/${offer.id}/deactivate" data-async="true" data-action="offer-deactivate" data-confirm="Деактивировать?">
                        <input type="hidden" name="_token" value="${csrf}">
                        <button class="btn btn-sm btn-danger">Деактивировать</button>
                    </form>
                </div>
            </td>
        `;
        return row;
    };

    const upsertOfferRow = (offer) => {
        const body = document.querySelector('[data-offers-body]');
        if (!body) return;
        const existing = body.querySelector(`[data-entity="offer"][data-id="${offer.id}"]`);
        const newRow = buildOfferRow(offer);
        if (!newRow) return;
        if (existing) {
            existing.replaceWith(newRow);
        } else {
            body.prepend(newRow);
        }
    };

    const removeOfferRow = (id) => {
        const body = document.querySelector('[data-offers-body]');
        if (!body) return;
        const row = body.querySelector(`[data-entity="offer"][data-id="${id}"]`);
        if (row) {
            row.remove();
        }
    };

    const fetchOffer = async (id) => {
        try {
            const response = await fetch(`/offers/${id}/json`, {
                headers: {
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            });
            if (!response.ok) return null;
            const data = await response.json();
            return data.offer || null;
        } catch (e) {
            console.error('fetch offer error', e);
            return null;
        }
    };

    const handleFormSubmit = (event) => {
        const form = event.target;
        if (!form.matches('[data-async="true"]')) return;
        if (!window.fetch) return;

        if (form.dataset.confirm && !confirm(form.dataset.confirm)) {
            event.preventDefault();
            return;
        }

        event.preventDefault();

        const errorBox = findErrorBox(form);
        const successBox = findSuccessBox(form);
        clearBox(errorBox);
        clearBox(successBox);

        const data = new FormData(form);
        const method = (form.getAttribute('method') || 'POST').toUpperCase();

        fetch(form.action, {
            method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: data,
            credentials: 'same-origin',
        })
            .then(async (response) => {
                if (response.ok) {
                    return response.json();
                }

                if (response.status === 422) {
                    const json = await response.json().catch(() => ({}));
                    const errors = json.errors ? Object.values(json.errors).flat() : [json.message || 'Ошибка валидации'];
                    if (errorBox) {
                        showBox(errorBox, `<ul class="mb-0">${errors.map((e) => `<li>${e}</li>`).join('')}</ul>`, 'error');
                    }
                    throw new Error('validation');
                }

                const json = await response.json().catch(() => ({}));
                const message = json.message || 'Ошибка запроса';
                if (errorBox) {
                    showBox(errorBox, message, 'error');
                }
                throw new Error(message);
            })
            .then((json) => handleSuccess(form, json, successBox))
            .catch((error) => {
                if (error.message === 'validation') return;
                console.error('submit error', error);
            });
    };

    const handleSuccess = (form, json, successBox) => {
        const action = form.dataset.action;
        if (!json) return;

        if (successBox && json.message) {
            showBox(successBox, json.message, 'success');
        }

        switch (action) {
            case 'offer-create':
                if (json.offer) {
                    upsertOfferRow(json.offer);
                    publish({ type: 'offer.created', id: json.offer.id });
                    form.reset();
                    showBox(successBox, 'Оффер создан', 'success');
                }
                break;
            case 'offer-status':
                if (json.offer) {
                    upsertOfferRow(json.offer);
                    publish({ type: 'offer.updated', id: json.offer.id });
                    showBox(successBox, 'Статус обновлен', 'success');
                }
                break;
            case 'offer-deactivate':
                if (json.id || json.offer?.id) {
                    const id = json.id || json.offer.id;
                    removeOfferRow(id);
                    publish({ type: 'offer.deleted', id });
                    showBox(successBox, 'Оффер деактивирован', 'success');
                }
                break;
            case 'subscription-subscribe':
                if (json.status === 'ok') {
                    const row = form.closest('[data-offer-id]');
                    if (row) row.remove();
                    showBox(successBox, 'Подписка оформлена', 'success');
                }
                break;
            case 'subscription-unsubscribe':
                if (json.ok) {
                    const row = form.closest('[data-subscription-id]');
                    if (row) row.remove();
                    showBox(successBox, 'Подписка отключена', 'success');
                }
                break;
            default:
                break;
        }
    };

    const attachListeners = () => {
        document.addEventListener('submit', handleFormSubmit);

        subscribe(async (event) => {
            if (!event || event.self === selfMark) return;
            if (!event.type || !event.id) return;

            if (event.type === 'offer.created' || event.type === 'offer.updated') {
                const offer = await fetchOffer(event.id);
                if (offer) {
                    upsertOfferRow(offer);
                }
            }

            if (event.type === 'offer.deleted') {
                removeOfferRow(event.id);
            }
        });
    };

    attachListeners();
})();
