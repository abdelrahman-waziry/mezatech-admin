/**
 * MezaTech Request Logger
 * 
 * Automatically logs ALL network requests made by the website.
 * This script intercepts fetch() and XMLHttpRequest calls.
 * 
 * Usage:
 * 1. Include this script in your HTML
 * 2. Initialize: MezaTech.init({ baseUrl: 'https://your-api.com/api/v1' });
 * 3. All network calls will be automatically logged!
 */

(function (global) {
    'use strict';

    const MezaTech = {
        config: {
            baseUrl: 'https://mezatech-dashboard.test/api/v1',
            debug: true,
            enabled: false
        },

        // Store original functions
        _originalFetch: global.fetch,
        _originalXHROpen: XMLHttpRequest.prototype.open,
        _originalXHRSend: XMLHttpRequest.prototype.send,

        /**
         * Initialize the SDK and start intercepting network calls
         * @param {Object} options - Configuration options
         * @param {string} options.baseUrl - The base URL of the MezaTech API
         * @param {boolean} options.debug - Enable console logging (default: true)
         */
        init: function (options = {}) {
            this.config = { ...this.config, ...options, enabled: true };

            if (this.config.debug) {
                console.log('[MezaTech] Initialized - Intercepting all network requests');
            }

            this._interceptFetch();
            this._interceptXHR();
        },

        /**
         * Send tracked request to MezaTech API
         */
        _sendToAPI: async function (requestData) {
            // Don't log our own API calls to prevent infinite loop
            if (requestData.endpoint.includes('/analytics/requests')) {
                return;
            }

            const payload = {
                endpoint: requestData.endpoint,
                method: requestData.method || 'GET',
                timestamp: new Date().toISOString(),
                app_source: 'web',
                app_version: '1.0.0',
                device: {
                    os: this._getOS(),
                    model: navigator.userAgent.substring(0, 100),
                    network: navigator.onLine ? 'online' : 'offline'
                },
                response: {
                    status: requestData.status || 0,
                    duration_ms: requestData.duration_ms || 0,
                    error_type: requestData.error_type || null
                }
            };

            if (this.config.debug) {
                console.log('[MezaTech] Logging:', payload);
            }

            try {
                // Use original fetch to avoid recursion
                this._originalFetch.call(global, `${this.config.baseUrl}/analytics/requests`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
            } catch (error) {
                if (this.config.debug) {
                    console.error('[MezaTech] Failed to send:', error.message);
                }
            }
        },

        /**
         * Intercept fetch() calls
         */
        _interceptFetch: function () {
            const self = this;

            global.fetch = async function (url, options = {}) {
                if (!self.config.enabled) {
                    return self._originalFetch.call(global, url, options);
                }

                const startTime = performance.now();
                const method = options.method || 'GET';
                const endpoint = typeof url === 'string' ? url : url.toString();

                try {
                    const response = await self._originalFetch.call(global, url, options);
                    const duration = Math.round(performance.now() - startTime);

                    // Log the request
                    self._sendToAPI({
                        endpoint: endpoint,
                        method: method,
                        status: response.status,
                        duration_ms: duration,
                        error_type: response.ok ? null : self._getErrorType(response.status)
                    });

                    return response;
                } catch (error) {
                    const duration = Math.round(performance.now() - startTime);

                    // Log failed request
                    self._sendToAPI({
                        endpoint: endpoint,
                        method: method,
                        status: 0,
                        duration_ms: duration,
                        error_type: 'server_error'
                    });

                    throw error;
                }
            };
        },

        /**
         * Intercept XMLHttpRequest calls
         */
        _interceptXHR: function () {
            const self = this;

            XMLHttpRequest.prototype.open = function (method, url, async, user, password) {
                this._mezatech = {
                    method: method,
                    url: url,
                    startTime: null
                };
                return self._originalXHROpen.apply(this, arguments);
            };

            XMLHttpRequest.prototype.send = function (body) {
                if (!self.config.enabled) {
                    return self._originalXHRSend.apply(this, arguments);
                }

                const xhr = this;
                xhr._mezatech.startTime = performance.now();

                xhr.addEventListener('loadend', function () {
                    const duration = Math.round(performance.now() - xhr._mezatech.startTime);
                    const endpoint = xhr._mezatech.url;

                    // Don't log our own API calls
                    if (endpoint.includes('/analytics/requests')) {
                        return;
                    }

                    self._sendToAPI({
                        endpoint: endpoint,
                        method: xhr._mezatech.method,
                        status: xhr.status,
                        duration_ms: duration,
                        error_type: xhr.status >= 400 ? self._getErrorType(xhr.status) : null
                    });
                });

                return self._originalXHRSend.apply(this, arguments);
            };
        },

        /**
         * Get error type based on status code
         */
        _getErrorType: function (status) {
            if (status >= 400 && status < 500) {
                if (status === 401 || status === 403) return 'auth_error';
                if (status === 422) return 'validation_error';
                return 'validation_error';
            }
            if (status >= 500) return 'server_error';
            return null;
        },

        /**
         * Get operating system
         */
        _getOS: function () {
            const ua = navigator.userAgent;
            if (ua.includes('Win')) return 'Windows';
            if (ua.includes('Mac')) return 'macOS';
            if (ua.includes('Linux')) return 'Linux';
            if (ua.includes('Android')) return 'Android';
            if (ua.includes('iPhone') || ua.includes('iPad')) return 'iOS';
            return 'Unknown';
        }
    };

    // Expose to global scope
    global.MezaTech = MezaTech;

})(typeof window !== 'undefined' ? window : this);
