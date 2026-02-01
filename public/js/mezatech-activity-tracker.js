/**
 * MezaTech Analytics Tracker
 * 
 * A lightweight JavaScript SDK for tracking analytics events and API requests.
 * This script can be embedded on any external website.
 * 
 * Usage:
 * 1. Include this script in your HTML:
 *    <script src="https://your-domain.com/js/mezatech-activity-tracker.js"></script>
 * 
 * 2. Initialize with your API base URL:
 *    MezaTech.init({ baseUrl: 'https://your-api-domain.com/api/v1' });
 * 
 * 3. Track events:
 *    MezaTech.trackEvent('tradein_started', { brand: 'Apple', model: 'iPhone 15' });
 */

(function (global) {
    'use strict';

    const MezaTech = {
        config: {
            baseUrl: 'https://mezatech-dashboard.test/api/v1',
            debug: false
        },

        /**
         * Initialize the SDK with custom configuration
         * @param {Object} options - Configuration options
         * @param {string} options.baseUrl - The base URL of the MezaTech API
         * @param {boolean} options.debug - Enable debug logging
         */
        init: function (options = {}) {
            this.config = { ...this.config, ...options };
            if (this.config.debug) {
                console.log('[MezaTech] Initialized with config:', this.config);
            }
        },

        /**
         * Track an analytics event
         * @param {string} eventName - Event name: 'tradein_started', 'tradein_completed', 'requote_requested', 'quote_viewed'
         * @param {Object} options - Event options
         * @param {Object} options.context - Context data (brand, model, condition, quoted_price)
         * @param {string} options.context.brand - Device brand (e.g., 'Apple', 'Samsung')
         * @param {string} options.context.model - Device model (e.g., 'iPhone 15 Pro')
         * @param {string} options.context.condition - Device condition: 'excellent', 'good', 'fair', 'damaged'
         * @param {number} options.context.quoted_price - Quoted trade-in price
         * @param {Object} options.location - Location data
         * @param {string} options.location.country - Country name
         * @param {string} options.location.city - City name
         * @param {string} options.location.area - Area (optional)
         * @param {string} options.location.district - District (optional)
         * @param {Object} options.device - Device data (auto-detected if not provided)
         * @param {string} options.userId - Optional user ID
         * @returns {Promise<Object>} - API response
         */
        trackEvent: async function (eventName, options = {}) {
            const payload = {
                event_name: eventName,
                timestamp: new Date().toISOString(),
                user_id: options.userId || null,
                context: options.context || {},
                location: options.location || {},
                device: options.device || this._getDeviceInfo()
            };

            return this._request('/analytics/events', 'POST', payload);
        },

        /**
         * Track an API request for analytics
         * @param {Object} requestData - Request data
         * @param {string} requestData.endpoint - The API endpoint that was called
         * @param {string} requestData.method - HTTP method: 'GET', 'POST', 'PUT', 'DELETE'
         * @param {string} requestData.appSource - App source: 'web', 'ios', 'android', 'partner'
         * @param {string} requestData.appVersion - App version string
         * @param {Object} requestData.response - Response data
         * @param {number} requestData.response.status - HTTP status code
         * @param {number} requestData.response.duration_ms - Response time in milliseconds
         * @param {string} requestData.response.error_type - Error type if any: 'validation_error', 'auth_error', 'server_error'
         * @returns {Promise<Object>} - API response
         */
        trackRequest: async function (requestData) {
            const payload = {
                endpoint: requestData.endpoint,
                method: requestData.method || 'GET',
                timestamp: requestData.timestamp || new Date().toISOString(),
                app_source: requestData.appSource || 'web',
                app_version: requestData.appVersion || '1.0.0',
                device: {
                    os: requestData.device?.os || this._getOS(),
                    model: requestData.device?.model || navigator.userAgent.substring(0, 100),
                    network: requestData.device?.network || 'unknown'
                },
                response: {
                    status: requestData.response?.status || 200,
                    duration_ms: requestData.response?.duration_ms || 0,
                    error_type: requestData.response?.error_type || null
                }
            };

            return this._request('/analytics/requests', 'POST', payload);
        },

        // ============ Private Methods ============

        /**
         * Internal request method
         * @private
         */
        _request: async function (endpoint, method, data) {
            const url = `${this.config.baseUrl}${endpoint}`;

            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            };

            try {
                if (this.config.debug) {
                    console.log(`[MezaTech] ${method} ${url}`, data);
                }

                const response = await fetch(url, options);
                const result = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw {
                        status: response.status,
                        message: result.message || 'Request failed',
                        errors: result.errors || null
                    };
                }

                if (this.config.debug) {
                    console.log('[MezaTech] Response:', result);
                }

                return { success: true, status: response.status, data: result };
            } catch (error) {
                if (this.config.debug) {
                    console.error('[MezaTech] Error:', error);
                }
                return {
                    success: false,
                    status: error.status || 0,
                    message: error.message || 'Network error',
                    errors: error.errors || null
                };
            }
        },


        /**
         * Get basic device information
         * @private
         */
        _getDeviceInfo: function () {
            return {
                brand: this._getBrowserName(),
                model: navigator.userAgent.substring(0, 100),
                os_version: this._getOS()
            };
        },

        /**
         * Get operating system name
         * @private
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
         * Get browser name
         * @private
         */
        _getBrowserName: function () {
            const ua = navigator.userAgent;
            if (ua.includes('Chrome') && !ua.includes('Edg')) return 'Chrome';
            if (ua.includes('Firefox')) return 'Firefox';
            if (ua.includes('Safari') && !ua.includes('Chrome')) return 'Safari';
            if (ua.includes('Edg')) return 'Edge';
            if (ua.includes('Opera') || ua.includes('OPR')) return 'Opera';
            return 'Unknown';
        }
    };

    // Expose to global scope
    global.MezaTech = MezaTech;

})(typeof window !== 'undefined' ? window : this);
