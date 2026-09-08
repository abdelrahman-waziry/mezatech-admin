import { html } from 'lit';

export function resultSummaryStep(state, onAccept, onReject, t) {
    const { result } = state;

    return html`
        <div class="ti-result">
            <p class="ti-eyebrow"><span>${t('offer')}</span></p>
            <h2 class="ti-title">${t('valuation')}</h2>
            <div class="ti-result-value">${result.finalPrice.toFixed(2)} ${t('currency')}</div>
            ${result.variantPrice !== undefined && result.partsTotal !== undefined
                ? html`<p class="ti-result-breakdown">
                    ${t('device-value')}: ${result.variantPrice.toFixed(2)} ${t('currency')} + ${t('parts-adjustment')}: ${result.partsTotal.toFixed(2)} ${t('currency')}
                </p>`
                : null}

            <div class="ti-actions">
                <button class="ti-btn is-secondary" @click=${onReject}>${t('decline')}</button>
                <button class="ti-btn is-primary" @click=${onAccept}>${t('accept-offer')}</button>
            </div>
        </div>
    `;
}

export function notEligibleStep(dispatch, t) {
    return html`
        <div class="ti-result">
            <p class="ti-eyebrow"><span>${t('result')}</span></p>
            <h2 class="ti-title">${t('not-eligible')}</h2>
            <p class="ti-subtitle">${t('not-eligible-subtitle')}</p>
            <div class="ti-actions">
                <span></span>
                <button class="ti-btn is-primary" @click=${() => dispatch({ type: 'RESTART' })}>${t('start-over')}</button>
            </div>
        </div>
    `;
}
