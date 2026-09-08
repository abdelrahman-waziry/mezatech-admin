import { html } from 'lit';

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const PHONE_RE = /^[0-9]{7,15}$/;

export function contactFormValid(customer) {
    return Boolean(customer.name?.trim()) && EMAIL_RE.test(customer.email ?? '') && PHONE_RE.test(customer.phoneNumber ?? '');
}

export function contactStep(state, dispatch, onSubmit, t) {
    const { customer } = state;

    return html`
        <p class="ti-eyebrow"><span>${t('almost-done')}</span></p>
        <h2 class="ti-title">${t('details')}</h2>
        <p class="ti-subtitle">${t('contact-subtitle')}</p>
        ${state.error ? html`<p class="ti-error">⚠ ${state.error}</p>` : null}

        <div class="ti-field">
            <label class="ti-label" for="ti-name">${t('full-name')}</label>
            <input
                id="ti-name"
                class="ti-input"
                type="text"
                .value=${customer.name}
                @input=${(e) => dispatch({ type: 'UPDATE_CUSTOMER', customer: { name: e.target.value } })}
            />
        </div>

        <div class="ti-field">
            <label class="ti-label" for="ti-email">${t('email')}</label>
            <input
                id="ti-email"
                class="ti-input"
                type="email"
                .value=${customer.email}
                @input=${(e) => dispatch({ type: 'UPDATE_CUSTOMER', customer: { email: e.target.value } })}
            />
        </div>

        <div class="ti-field">
            <label class="ti-label" for="ti-phone">${t('phone-number')}</label>
            <input
                id="ti-phone"
                class="ti-input"
                type="tel"
                .value=${customer.phoneNumber}
                @input=${(e) => dispatch({ type: 'UPDATE_CUSTOMER', customer: { phoneNumber: e.target.value } })}
            />
        </div>

        <div class="ti-actions">
            <button class="ti-btn is-ghost" ?disabled=${state.loading} @click=${() => dispatch({ type: 'BACK' })}>← ${t('back')}</button>
            <button
                class="ti-btn is-primary"
                ?disabled=${state.loading || !contactFormValid(customer)}
                @click=${onSubmit}
            >
                ${state.loading ? html`<span class="ti-spinner"></span>${t('calculating')}` : t('get-quote')}
            </button>
        </div>
    `;
}
