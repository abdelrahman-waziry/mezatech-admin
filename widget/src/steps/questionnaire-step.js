import { html } from 'lit';

export function questionnaireStep(state, dispatch, t, translateStep) {
    const steps = state.questionnaire?.steps ?? [];
    const step = steps[state.questionnaireStepIndex];

    if (!step) {
        return html`<p>${t('loading-questionnaire')}</p>`;
    }

    const localizedStep = translateStep(step);

    const currentAnswer = state.answers[step.key];
    const selectedOption = localizedStep.options.find((o) => o.value === currentAnswer?.value);
    const checkboxOptions = selectedOption?.conditional_checkboxes ?? [];

    function selectOption(option) {
        dispatch({ type: 'SET_ANSWER', key: step.key, value: option.value });
    }

    function toggleCheckbox(value, checked) {
        dispatch({ type: 'TOGGLE_CHECKBOX', key: step.key, value, checked });
    }

    return html`
        <div class="ti-progress-track">
            ${steps.map(
                (_, i) => html`<div class="ti-progress-dot ${i <= state.questionnaireStepIndex ? 'is-active' : ''}"></div>`
            )}
        </div>
        <p class="ti-eyebrow">
            <span>${localizedStep.title}</span>
            <span>${t('step')} ${state.questionnaireStepIndex + 1} ${t('of')} ${steps.length}</span>
        </p>

        <h2 class="ti-title">${localizedStep.question}</h2>
        ${state.error ? html`<p class="ti-error">⚠ ${state.error}</p>` : null}

        <div style="margin-top:1rem;">
            ${localizedStep.options.map(
                (option) => html`
                    <label class="ti-option ${currentAnswer?.value === option.value ? 'is-active' : ''}">
                        <input
                            type="radio"
                            name=${step.key}
                            .checked=${currentAnswer?.value === option.value}
                            @change=${() => selectOption(option)}
                        />
                        ${option.label}
                    </label>
                `
            )}
        </div>
        ${checkboxOptions.length
            ? html`
                  <div class="ti-checkbox-group">
                      ${checkboxOptions.map(
                          (cb) => html`
                              <label>
                                  <input
                                      type="checkbox"
                                      .checked=${(currentAnswer?.checkboxes ?? []).includes(cb.value)}
                                      @change=${(e) => toggleCheckbox(cb.value, e.target.checked)}
                                  />
                                  ${cb.label}
                              </label>
                          `
                      )}
                  </div>
              `
            : null}

        <div class="ti-actions">
            <button class="ti-btn is-ghost" @click=${() => dispatch({ type: 'BACK' })}>← ${t('back')}</button>
            <button
                class="ti-btn is-primary"
                ?disabled=${!currentAnswer}
                @click=${() => dispatch({ type: 'QUESTIONNAIRE_NEXT' })}
            >
                ${t('next')}
            </button>
        </div>
    `;
}

