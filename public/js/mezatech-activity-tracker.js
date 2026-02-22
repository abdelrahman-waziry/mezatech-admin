/**
 * MezaTech Analytics SDK
 * 
 * Automatically logs ALL network requests made by the website
 * and provides trackEvent() for business event tracking.
 * 
 * Usage:
 * 1. Include this script in your HTML
 * 2. Initialize: MezaTech.init({ baseUrl: 'https://your-api.com/api/v1' });
 * 3. All network calls will be automatically logged!
 * 4. Track events: MezaTech.trackEvent('tradein_started', { context: { brand: 'Apple', model: 'iPhone 15' }, location: { country: 'Egypt', city: 'Cairo' } });
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
         * Track an analytics event
         * @param {string} eventName - Event name: 'tradein_started', 'tradein_completed', 'requote_requested', 'quote_viewed'
         * @param {Object} options - Event options
         * @param {Object} options.context - Context data (required)
         * @param {string} options.context.brand - Device brand, e.g. 'Apple', 'Samsung' (required)
         * @param {string} options.context.model - Device model, e.g. 'iPhone 15 Pro' (required)
         * @param {string} options.context.condition - Condition: 'excellent', 'good', 'fair', 'damaged' (required for tradein_completed)
         * @param {number} options.context.quoted_price - Quoted trade-in price (optional)
         * @param {Object} options.location - Location data (required)
         * @param {string} options.location.country - Country name (required)
         * @param {string} options.location.city - City name (required)
         * @param {string} options.location.area - Area (optional)
         * @param {string} options.location.district - District (optional)
         * @param {string} options.userId - Optional user ID
         * @returns {Promise<Object>} - API response
         */
        trackEvent: async function (eventName, options = {}) {
            // Validate event name
            const validEvents = ['tradein_started', 'tradein_completed', 'requote_requested', 'quote_viewed'];
            if (!validEvents.includes(eventName)) {
                console.error('[MezaTech] Invalid event_name. Must be one of:', validEvents.join(', '));
                return { success: false, message: 'Invalid event_name' };
            }

            // Validate condition enum if provided
            const validConditions = ['excellent', 'good', 'fair', 'damaged'];
            if (options.context?.condition && !validConditions.includes(options.context.condition)) {
                console.error('[MezaTech] Invalid condition. Must be one of:', validConditions.join(', '));
                return { success: false, message: 'Invalid condition' };
            }

            const payload = {
                event_name: eventName,
                timestamp: this._getTimestamp(),
                user_id: options.userId || null,
                context: {
                    brand: options.context?.brand || '',
                    model: options.context?.model || '',
                    condition: options.context?.condition || null,
                    quoted_price: options.context?.quoted_price != null ? parseFloat(options.context.quoted_price) : null
                },
                location: {
                    country: options.location?.country || '',
                    city: options.location?.city || '',
                    area: options.location?.area || null,
                    district: options.location?.district || null
                },
                device: {
                    brand: this._getBrowserName(),
                    model: navigator.userAgent.substring(0, 100),
                    os_version: this._getOS()
                }
            };

            if (this.config.debug) {
                console.log('[MezaTech] Tracking event:', payload);
            }

            try {
                const response = await this._originalFetch.call(global, `${this.config.baseUrl}/analytics/events`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json().catch(() => ({}));

                if (this.config.debug) {
                    console.log('[MezaTech] Event response:', { success: response.ok, status: response.status, data: result });
                }

                return { success: response.ok, status: response.status, data: result };
            } catch (error) {
                if (this.config.debug) {
                    console.error('[MezaTech] Failed to track event:', error.message);
                }
                return { success: false, message: error.message };
            }
        },

        /**
         * Send tracked request to MezaTech API
         */
        _sendToAPI: async function (requestData) {
            // Don't log our own API calls to prevent infinite loop
            if (requestData.endpoint.includes('/analytics/requests') || requestData.endpoint.includes('/analytics/events')) {
                return;
            }

            // Ensure method is a valid enum value (GET, POST, PUT, DELETE)
            const validMethods = ['GET', 'POST', 'PUT', 'DELETE'];
            const method = validMethods.includes((requestData.method || '').toUpperCase())
                ? requestData.method.toUpperCase()
                : 'GET';

            const payload = {
                request_id: this._generateGUID(),
                endpoint: requestData.endpoint,
                method: method,
                timestamp: this._getTimestamp(),
                app_source: 'web',
                app_version: '1.0.0',
                device: {
                    os: this._getOS(),
                    model: navigator.userAgent.substring(0, 100),
                    network: this._getNetwork()
                },
                response: {
                    status: parseInt(requestData.status, 10) || 0,
                    duration_ms: parseInt(requestData.duration_ms, 10) || 0,
                    error_type: requestData.error_type || null
                }
            };

            if (this.config.debug) {
                console.log('[MezaTech] Logging request:', payload);
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
                    if (endpoint.includes('/analytics/requests') || endpoint.includes('/analytics/events')) {
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
        },

        /**
         * Get browser name for device.brand
         */
        _getBrowserName: function () {
            const ua = navigator.userAgent;
            if (ua.includes('Chrome') && !ua.includes('Edg')) return 'Chrome';
            if (ua.includes('Firefox')) return 'Firefox';
            if (ua.includes('Safari') && !ua.includes('Chrome')) return 'Safari';
            if (ua.includes('Edg')) return 'Edge';
            if (ua.includes('Opera') || ua.includes('OPR')) return 'Opera';
            return 'Unknown';
        },

        /**
         * Get ISO-8601 timestamp with timezone offset (format: Y-m-d\TH:i:sP)
         * e.g. "2026-02-01T16:30:00+02:00"
         */
        _getTimestamp: function () {
            const now = new Date();
            const offset = -now.getTimezoneOffset();
            const sign = offset >= 0 ? '+' : '-';
            const absOffset = Math.abs(offset);
            const hours = String(Math.floor(absOffset / 60)).padStart(2, '0');
            const minutes = String(absOffset % 60).padStart(2, '0');

            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');

            return `${year}-${month}-${day}T${h}:${m}:${s}${sign}${hours}:${minutes}`;
        },

        /**
         * Get network type: 'wifi', 'cellular', or 'unknown'
         * Uses the Network Information API when available
         */
        _getNetwork: function () {
            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            if (connection && connection.type) {
                if (connection.type === 'wifi') return 'wifi';
                if (connection.type === 'cellular') return 'cellular';
            }
            return 'unknown';
        },

        /**
         * Generate a GUID (UUID v4)
         */
        _generateGUID: function () {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                const r = Math.random() * 16 | 0;
                const v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }
    };

    // Expose to global scope
    global.MezaTech = MezaTech;

})(typeof window !== 'undefined' ? window : this);
