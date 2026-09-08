export const STEPS = Object.freeze({
    DEVICE_SELECT: 'DEVICE_SELECT',
    QUESTIONNAIRE: 'QUESTIONNAIRE',
    PARTS_SELECT: 'PARTS_SELECT',
    CONTACT: 'CONTACT',
    RESULT: 'RESULT',
    NOT_ELIGIBLE: 'NOT_ELIGIBLE',
});

export function createInitialState() {
    return {
        step: STEPS.DEVICE_SELECT,
        loading: false,
        error: null,

        products: [],
        variants: [],
        selectedProductId: null,
        selectedVariantId: null,

        questionnaire: null,
        questionnaireStepIndex: 0,
        answers: {}, // key -> { value, checkboxes: string[] }
        needsPartsSelection: false,

        parts: [],
        partsWithValuesMap: {},
        repairedPartsBefore: [],

        customer: { name: '', email: '', phoneNumber: '' },

        result: null,

        // stack of { step, questionnaireStepIndex } snapshots for generic Back navigation
        history: [],
    };
}

function currentStepDef(state) {
    return state.questionnaire?.steps?.[state.questionnaireStepIndex] ?? null;
}

function pushHistory(state) {
    return [...state.history, { step: state.step, questionnaireStepIndex: state.questionnaireStepIndex }];
}

export function reducer(state, action) {
    switch (action.type) {
        case 'LOADING':
            return { ...state, loading: action.value, error: null };

        case 'ERROR':
            return { ...state, loading: false, error: action.message };

        case 'PRODUCTS_LOADED':
            return { ...state, products: action.products };

        case 'QUESTIONNAIRE_LOADED':
            return { ...state, questionnaire: action.questionnaire };

        case 'PARTS_LOADED':
            return { ...state, parts: action.parts };

        case 'SELECT_PRODUCT':
            return {
                ...state,
                selectedProductId: action.productId,
                selectedVariantId: null,
                variants: [],
            };

        case 'VARIANTS_LOADED':
            return { ...state, variants: action.variants };

        case 'SELECT_VARIANT':
            return { ...state, selectedVariantId: action.variantId };

        case 'GO_TO_QUESTIONNAIRE':
            return {
                ...state,
                history: pushHistory(state),
                step: STEPS.QUESTIONNAIRE,
                questionnaireStepIndex: 0,
            };

        // Records the chosen radio value only — does NOT navigate. Resets any conditional checkboxes.
        case 'SET_ANSWER':
            return {
                ...state,
                answers: { ...state.answers, [action.key]: { value: action.value, checkboxes: [] } },
            };

        case 'TOGGLE_CHECKBOX': {
            const existing = state.answers[action.key] ?? { value: null, checkboxes: [] };
            const checkboxes = action.checked
                ? [...existing.checkboxes, action.value]
                : existing.checkboxes.filter((v) => v !== action.value);

            return {
                ...state,
                answers: { ...state.answers, [action.key]: { ...existing, checkboxes } },
            };
        }

        case 'QUESTIONNAIRE_NEXT': {
            const step = currentStepDef(state);
            const answer = state.answers[step.key];

            if (!answer) {
                return state;
            }

            const selectedOption = step.options.find((o) => o.value === answer.value);
            const steps = state.questionnaire.steps;
            const isLastStep = state.questionnaireStepIndex >= steps.length - 1;
            const history = pushHistory(state);

            if (selectedOption?.hard_stop) {
                return { ...state, history, step: STEPS.NOT_ELIGIBLE };
            }

            if (selectedOption?.triggers_parts_selection) {
                return {
                    ...state,
                    history,
                    needsPartsSelection: true,
                    step: STEPS.PARTS_SELECT,
                    // stash where to resume the questionnaire once parts selection is done
                    questionnaireStepIndex: isLastStep ? steps.length : state.questionnaireStepIndex + 1,
                };
            }

            if (isLastStep) {
                return { ...state, history, step: STEPS.CONTACT };
            }

            return { ...state, history, questionnaireStepIndex: state.questionnaireStepIndex + 1 };
        }

        case 'PARTS_DONE': {
            const steps = state.questionnaire?.steps ?? [];
            const resumeIndex = state.questionnaireStepIndex;
            const history = pushHistory(state);

            if (resumeIndex >= steps.length) {
                return { ...state, history, step: STEPS.CONTACT };
            }

            return { ...state, history, step: STEPS.QUESTIONNAIRE, questionnaireStepIndex: resumeIndex };
        }

        case 'SET_PART_VALUE':
            return {
                ...state,
                partsWithValuesMap: { ...state.partsWithValuesMap, [action.partId]: action.value },
            };

        case 'SET_REPAIRED_PARTS_BEFORE':
            return { ...state, repairedPartsBefore: action.partIds };

        // Generic Back: pop the last snapshot and restore it.
        case 'BACK': {
            if (state.history.length === 0) {
                return state;
            }

            const previous = state.history[state.history.length - 1];

            return {
                ...state,
                step: previous.step,
                questionnaireStepIndex: previous.questionnaireStepIndex,
                history: state.history.slice(0, -1),
            };
        }

        case 'UPDATE_CUSTOMER':
            return { ...state, customer: { ...state.customer, ...action.customer } };

        case 'RESULT_LOADED':
            return { ...state, step: STEPS.RESULT, result: action.result };

        case 'RESTART':
            return createInitialState();

        default:
            return state;
    }
}
