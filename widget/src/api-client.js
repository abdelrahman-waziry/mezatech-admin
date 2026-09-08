export class ApiError extends Error {
    constructor(message, status, code = null) {
        super(message);
        this.status = status;
        this.code = code;
    }
}

export class ApiClient {
    constructor(apiBase, clientId) {
        this.apiBase = apiBase.replace(/\/$/, '');
        this.clientId = clientId;
    }

    async request(path, options = {}) {
        const res = await fetch(`${this.apiBase}${path}`, {
            ...options,
            headers: {
                Accept: 'application/json',
                'X-Client-Id': this.clientId,
                ...(options.body ? { 'Content-Type': 'application/json' } : {}),
                ...options.headers,
            },
        });

        if (res.status === 429) {
            throw new ApiError('Too many requests, please try again shortly.', 429);
        }

        const json = await res.json().catch(() => null);

        if (!res.ok) {
            throw new ApiError(json?.message ?? `Request failed (${res.status})`, res.status, json?.code ?? null);
        }

        return json?.data;
    }

    getProducts() {
        return this.request('/products');
    }

    getVariants(productId) {
        return this.request(`/products/${productId}/variants`);
    }

    getParts(productId) {
        return this.request(`/parts?productId=${encodeURIComponent(productId)}`);
    }

    getQuestionnaire() {
        return this.request('/questionnaires/trade-in');
    }

    calculate(payload) {
        return this.request('/calculate', {
            method: 'POST',
            body: JSON.stringify(payload),
        });
    }
}
