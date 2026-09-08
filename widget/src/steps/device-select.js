import { html } from 'lit';

export function deviceSelectStep(state, dispatch, t) {
    return html`
        <p class="ti-eyebrow"><span>${t('trade-in')}</span></p>
        <h2 class="ti-title">${t('select-device')}</h2>
        <p class="ti-subtitle">${t('choose-device')}</p>
        ${state.error ? html`<p class="ti-error">⚠ ${state.error}</p>` : null}

        <div class="ti-field">
            <label class="ti-label" for="ti-model">${t('model')}</label>
            <select
                id="ti-model"
                class="ti-select"
                .value=${state.selectedProductId ?? ''}
                @change=${(e) => dispatch({ type: 'SELECT_PRODUCT', productId: e.target.value })}
            >
                <option value="" disabled selected>${t('choose-model')}</option>
                ${state.products.map(
                    (p) => html`<option value=${p.id}>${p.brand_name ? `${p.brand_name} ` : ''}${p.name}</option>`
                )}
            </select>
        </div>

        ${state.selectedProductId
            ? html`
                  <div class="ti-field">
                          <label class="ti-label" for="ti-variant">${t('variant')}</label>
                      <select
                          id="ti-variant"
                          class="ti-select"
                          .value=${state.selectedVariantId ?? ''}
                          @change=${(e) => dispatch({ type: 'SELECT_VARIANT', variantId: e.target.value })}
                      >
                          <option value="" disabled selected>${t('choose-variant')}</option>
                          ${state.variants.map((v) => html`<option value=${v.id}>${v.name}</option>`)}
                      </select>
                  </div>
              `
            : null}

        <div class="ti-actions">
            <span></span>
            <button
                class="ti-btn is-primary"
                ?disabled=${!state.selectedVariantId}
                @click=${() => dispatch({ type: 'GO_TO_QUESTIONNAIRE' })}
            >
                ${t('continue')}
            </button>
        </div>
    `;
}

