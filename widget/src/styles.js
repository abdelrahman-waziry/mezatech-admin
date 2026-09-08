import { css } from 'lit';

export const widgetStyles = css`
    :host {
        display: block;
        width: 100%;
        box-sizing: border-box;
        font-family: 'IBM Plex Sans Arabic', sans-serif;
        --ti-primary: #2563eb;
        --ti-bg: #ffffff;
        --ti-text: #111827;
        --ti-radius: 14px;
        --ti-muted: #6b7280;
        --ti-border: rgba(17, 24, 39, 0.1);
        --ti-surface: color-mix(in srgb, var(--ti-primary) 6%, var(--ti-bg));
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    .ti-card {
        font-family: inherit;
        background: var(--ti-bg);
        color: var(--ti-text);
        border-radius: var(--ti-radius);
        border: 1px solid var(--ti-border);
        padding: 1.5rem;
        max-width: 480px;
        margin: 0 auto;
        box-shadow: 0 8px 24px rgba(17, 24, 39, 0.08);
        animation: ti-fade-in 0.2s ease;
    }

    .ti-card[dir='rtl'] {
        text-align: right;
    }

    .ti-card[dir='rtl'] .ti-checkbox-group {
        margin-left: 0;
        margin-right: 1.6rem;
    }

    .ti-card button,
    .ti-card input,
    .ti-card select,
    .ti-card textarea {
        font-family: inherit;
    }

    @keyframes ti-fade-in {
        from {
            opacity: 0;
            transform: translateY(4px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .ti-eyebrow {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--ti-muted);
        margin-bottom: 0.75rem;
    }

    .ti-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0 0 0.25rem;
        letter-spacing: -0.01em;
    }

    .ti-subtitle {
        color: var(--ti-muted);
        font-size: 0.9rem;
        margin: 0 0 1.25rem;
    }

    .ti-progress-track {
        display: flex;
        gap: 4px;
        margin-bottom: 1.25rem;
    }

    .ti-progress-dot {
        flex: 1;
        height: 5px;
        border-radius: 999px;
        background: var(--ti-border);
        transition: background 0.2s ease;
    }

    .ti-progress-dot.is-active {
        background: var(--ti-primary);
    }

    .ti-field {
        margin-bottom: 1.1rem;
    }

    .ti-label {
        display: block;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 0.4rem;
    }

    .ti-select,
    .ti-input {
        width: 100%;
        padding: 0.7rem 0.85rem;
        border-radius: calc(var(--ti-radius) - 6px);
        border: 1.5px solid var(--ti-border);
        font-size: 0.95rem;
        background: var(--ti-bg);
        color: var(--ti-text);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .ti-select {
        padding-right: 2.5rem;
    }

    .ti-card[dir='rtl'] .ti-select {
        padding-right: 0.85rem;
        padding-left: 2.5rem;
    }

    .ti-select:focus-visible,
    .ti-input:focus-visible {
        outline: none;
        border-color: var(--ti-primary);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--ti-primary) 20%, transparent);
    }

    .ti-option {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.75rem 0.9rem;
        border-radius: calc(var(--ti-radius) - 4px);
        border: 1.5px solid var(--ti-border);
        margin-bottom: 0.6rem;
        cursor: pointer;
        font-size: 0.92rem;
        transition: border-color 0.15s ease, background 0.15s ease, transform 0.1s ease;
    }

    .ti-option:hover {
        border-color: color-mix(in srgb, var(--ti-primary) 45%, var(--ti-border));
    }

    .ti-option:active {
        transform: scale(0.99);
    }

    .ti-option.is-active {
        border-color: var(--ti-primary);
        background: var(--ti-surface);
        font-weight: 600;
    }

    .ti-option input[type='radio'] {
        accent-color: var(--ti-primary);
        width: 1.05rem;
        height: 1.05rem;
        flex-shrink: 0;
    }

    .ti-checkbox-group {
        margin: 0.4rem 0 1.1rem 1.6rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .ti-checkbox-group label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        cursor: pointer;
    }

    .ti-checkbox-group input[type='checkbox'] {
        accent-color: var(--ti-primary);
        width: 1rem;
        height: 1rem;
    }

    .ti-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }

    .ti-btn {
        padding: 0.7rem 1.2rem;
        border-radius: calc(var(--ti-radius) - 4px);
        border: none;
        font-size: 0.92rem;
        font-weight: 600;
        cursor: pointer;
        transition: filter 0.15s ease, transform 0.1s ease, background 0.15s ease;
    }

    .ti-btn:active {
        transform: scale(0.98);
    }

    .ti-btn.is-primary {
        background: var(--ti-primary);
        color: #fff;
        flex: 1;
    }

    .ti-btn.is-primary:hover:not(:disabled) {
        filter: brightness(0.92);
    }

    .ti-btn.is-secondary {
        background: transparent;
        color: var(--ti-muted);
        border: 1.5px solid var(--ti-border);
    }

    .ti-btn.is-secondary:hover:not(:disabled) {
        background: var(--ti-surface);
        color: var(--ti-text);
    }

    .ti-btn.is-ghost {
        background: transparent;
        color: var(--ti-muted);
        padding: 0.7rem 0.5rem;
    }

    .ti-btn.is-ghost:hover:not(:disabled) {
        color: var(--ti-text);
    }

    .ti-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .ti-error {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #b91c1c;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: calc(var(--ti-radius) - 6px);
        padding: 0.6rem 0.8rem;
        font-size: 0.85rem;
        margin-bottom: 0.9rem;
    }

    .ti-result {
        text-align: center;
    }

    .ti-result-value {
        font-size: 2.25rem;
        font-weight: 800;
        color: var(--ti-primary);
        margin: 0.4rem 0 0.25rem;
        letter-spacing: -0.02em;
    }

    .ti-result-breakdown {
        color: var(--ti-muted);
        font-size: 0.85rem;
        margin: 0 0 1.25rem;
    }

    .ti-spinner {
        width: 1.1rem;
        height: 1.1rem;
        border: 2px solid rgba(255, 255, 255, 0.4);
        border-top-color: #fff;
        border-radius: 50%;
        display: inline-block;
        vertical-align: -2px;
        margin-right: 0.4rem;
        animation: ti-spin 0.7s linear infinite;
    }

    @keyframes ti-spin {
        to {
            transform: rotate(360deg);
        }
    }

    .ti-part-card {
        border: 1.5px solid var(--ti-border);
        border-radius: calc(var(--ti-radius) - 4px);
        padding: 0.85rem 1rem;
        margin-bottom: 0.75rem;
    }

    .ti-part-checkbox {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.55rem;
        font-size: 0.85rem;
        color: var(--ti-muted);
    }

    @media (max-width: 480px) {
        .ti-card {
            padding: 1.15rem;
            border-radius: 0;
            box-shadow: none;
            border-left: none;
            border-right: none;
        }
    }
`;
