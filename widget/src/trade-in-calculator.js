import { LitElement, html } from 'lit';
import { widgetStyles } from './styles.js';
import { ApiClient } from './api-client.js';
import { STEPS, createInitialState, reducer } from './state-machine.js';
import { buildCalculatePayload } from './payload-builder.js';
import { deviceSelectStep } from './steps/device-select.js';
import { questionnaireStep } from './steps/questionnaire-step.js';
import { partsSelectStep } from './steps/parts-select.js';
import { contactStep } from './steps/contact-form.js';
import { resultSummaryStep, notEligibleStep } from './steps/result-summary.js';
import { createTranslator, isRtl, normalizeLanguage, translateQuestionnaireStep } from './i18n.js';

export class TradeInCalculator extends LitElement {
    static styles = widgetStyles;

    static properties = {
        apiBase: { attribute: 'api-base', type: String },
        clientId: { attribute: 'client-id', type: String },
        primaryColor: { attribute: 'primary-color', type: String },
        language: { attribute: 'language', type: String },
        lang: { attribute: 'lang', type: String },
    };

    constructor() {
        super();
        this.apiBase = '';
        this.clientId = '';
        this.primaryColor = '';
        this.language = 'en';
        this.lang = '';
        this._state = createInitialState();
    }

    connectedCallback() {
        super.connectedCallback();
        this._ensureFontLoaded();
        this._api = new ApiClient(this.apiBase, this.clientId);
        this._loadInitialData();
    }

    _ensureFontLoaded() {
        const fontId = 'trade-in-widget-ibm-plex-sans-arabic';
        if (document.getElementById(fontId)) return;

        const preconnect = document.createElement('link');
        preconnect.rel = 'preconnect';
        preconnect.href = 'https://fonts.googleapis.com';
        document.head.appendChild(preconnect);

        const fontLink = document.createElement('link');
        fontLink.id = fontId;
        fontLink.rel = 'stylesheet';
        fontLink.href = 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap';
        document.head.appendChild(fontLink);
    }

    attributeChangedCallback(name, oldValue, newValue) {
        super.attributeChangedCallback(name, oldValue, newValue);
        if (name === 'primary-color' && newValue) {
            this.style.setProperty('--ti-primary', newValue);
        }

        if ((name === 'language' || name === 'lang') && oldValue !== newValue) {
            this.requestUpdate();
        }
    }

    dispatch = (action) => {
        this._state = reducer(this._state, action);
        this.requestUpdate();

        if (action.type === 'SELECT_PRODUCT') {
            this._loadVariants(action.productId);
        }

        if (action.type === 'GO_TO_QUESTIONNAIRE' && this._state.selectedProductId) {
            this._loadParts(this._state.selectedProductId);
        }
    };

    async _loadInitialData() {
        this.dispatch({ type: 'LOADING', value: true });
        try {
            const [products, questionnaire] = await Promise.all([
                this._api.getProducts(),
                this._api.getQuestionnaire(),
            ]);
            this.dispatch({ type: 'PRODUCTS_LOADED', products });
            this.dispatch({ type: 'QUESTIONNAIRE_LOADED', questionnaire });
        } catch (err) {
            this.dispatch({ type: 'ERROR', message: this._friendlyError(err) });
        } finally {
            this.dispatch({ type: 'LOADING', value: false });
        }
    }

    async _loadVariants(productId) {
        this.dispatch({ type: 'LOADING', value: true });
        try {
            const variants = await this._api.getVariants(productId);
            this.dispatch({ type: 'VARIANTS_LOADED', variants });
        } catch (err) {
            this.dispatch({ type: 'ERROR', message: this._friendlyError(err) });
        } finally {
            this.dispatch({ type: 'LOADING', value: false });
        }
    }

    async _loadParts(productId) {
        this.dispatch({ type: 'LOADING', value: true });
        try {
            const parts = await this._api.getParts(productId);
            this.dispatch({ type: 'PARTS_LOADED', parts });
        } catch (err) {
            this.dispatch({ type: 'ERROR', message: this._friendlyError(err) });
        } finally {
            this.dispatch({ type: 'LOADING', value: false });
        }
    }

    async _submitCalculation() {
        this.dispatch({ type: 'LOADING', value: true });
        try {
            const payload = buildCalculatePayload(this._state);
            const result = await this._api.calculate(payload);
            this.dispatch({ type: 'RESULT_LOADED', result });
        } catch (err) {
            this.dispatch({ type: 'ERROR', message: this._friendlyError(err) });
        } finally {
            this.dispatch({ type: 'LOADING', value: false });
        }
    }

    _friendlyError(error) {
        const language = normalizeLanguage(this.getAttribute('language') || this.getAttribute('lang') || 'en');
        const t = createTranslator(language);
        const messages = {
            missing_client_id: 'error-missing-client',
            invalid_client_id: 'error-invalid-client',
            suspended_vendor: 'error-suspended-client',
            origin_not_allowed: 'error-origin',
        };

        if (error?.status === 429) return t('error-rate-limit');

        return t(messages[error?.code] ?? 'generic-error');
    }

    _emit(name, detail) {
        this.dispatchEvent(new CustomEvent(name, { detail, bubbles: true, composed: true }));

        const legacyName = name === 'trade-in:accepted' ? 'onaccepted' : name === 'trade-in:rejected' ? 'onrejected' : null;
        if (legacyName) {
            this.dispatchEvent(new CustomEvent(legacyName, { detail, bubbles: true, composed: true }));
        }
    }

    render() {
        const state = this._state;
        const language = normalizeLanguage(this.getAttribute('language') || this.getAttribute('lang') || 'en');
        const t = createTranslator(language);
        const translateStep = (step) => translateQuestionnaireStep(step, language);
        let body;

        switch (state.step) {
            case STEPS.DEVICE_SELECT:
                body = deviceSelectStep(state, this.dispatch, t);
                break;
            case STEPS.QUESTIONNAIRE:
                body = questionnaireStep(state, this.dispatch, t, translateStep);
                break;
            case STEPS.PARTS_SELECT:
                body = partsSelectStep(state, this.dispatch, t);
                break;
            case STEPS.CONTACT:
                body = contactStep(state, this.dispatch, () => this._submitCalculation(), t);
                break;
            case STEPS.RESULT:
                body = resultSummaryStep(
                    state,
                    () => this._emit('trade-in:accepted', { result: state.result, payload: buildCalculatePayload(state) }),
                    () => this._emit('trade-in:rejected', { result: state.result }),
                    t
                );
                break;
            case STEPS.NOT_ELIGIBLE:
                body = notEligibleStep(this.dispatch, t);
                break;
            default:
                body = html`<p>${t('generic-error')}</p>`;
        }

        return html`<div class="ti-card" dir=${isRtl(language) ? 'rtl' : 'ltr'}>${body}</div>`;
    }
}

customElements.define('trade-in-calculator', TradeInCalculator);
