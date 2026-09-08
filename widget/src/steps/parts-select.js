import { html } from 'lit';

export function partsSelectStep(state, dispatch, t) {
    const parts = state.parts.filter((p) => String(p.product_id) === String(state.selectedProductId) && ![0, 1, 2].includes(Number(p.type)));

    function toggleRepairedBefore(partId, checked) {
        const next = checked
            ? [...state.repairedPartsBefore, partId]
            : state.repairedPartsBefore.filter((id) => id !== partId);

        dispatch({ type: 'SET_REPAIRED_PARTS_BEFORE', partIds: next });
    }

    return html`
        <p class="ti-eyebrow"><span>${t('repair-details')}</span></p>
        <h2 class="ti-title">${t('repaired-parts')}</h2>
        <p class="ti-subtitle">${t('repair-subtitle')}</p>
        ${state.error ? html`<p class="ti-error">⚠ ${state.error}</p>` : null}

        ${parts.length === 0 ? html`<p class="ti-subtitle">${t('no-parts')}</p>` : null}

        ${parts.map(
            (part) => html`
                <div class="ti-part-card">
                    <label class="ti-label">${part.name}</label>
                    <label class="ti-part-checkbox">
                        <input
                            type="checkbox"
                            .checked=${state.repairedPartsBefore.includes(part.id)}
                            @change=${(e) => toggleRepairedBefore(part.id, e.target.checked)}
                        />
                        ${t('replaced-part')}
                    </label>
                </div>
            `
        )}

        <div class="ti-actions">
            <button class="ti-btn is-ghost" @click=${() => dispatch({ type: 'BACK' })}>← ${t('back')}</button>
            <button class="ti-btn is-primary" @click=${() => dispatch({ type: 'PARTS_DONE' })}>${t('continue')}</button>
        </div>
    `;
}

