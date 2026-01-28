/**
 * Loading State Utility
 * 
 * Provides delayed loading state management to prevent flicker
 * for fast operations (< 300ms).
 * 
 * Usage in Alpine.js:
 * 
 * x-data="{ 
 *     isLoading: false,
 *     ...loadingState(300, 200)
 * }"
 * 
 * Then in methods:
 *   this.startLoading('isLoading');
 *   // ... async operation
 *   this.stopLoading('isLoading');
 */

export function loadingState(delayMs = 300, minDisplayMs = 200) {
    const timers = {};
    const startTimes = {};

    return {
        /**
         * Start delayed loading
         * @param {string} key - The loading state property name
         */
        startLoading(key = 'isLoading') {
            startTimes[key] = Date.now();
            timers[key] = setTimeout(() => {
                this[key] = true;
            }, delayMs);
        },

        /**
         * Stop loading with minimum display time
         * @param {string} key - The loading state property name
         */
        stopLoading(key = 'isLoading') {
            clearTimeout(timers[key]);
            const elapsed = Date.now() - (startTimes[key] || 0);

            if (this[key]) {
                // If loading was shown, ensure minimum display time
                const displayedFor = elapsed - delayMs;
                const remaining = Math.max(0, minDisplayMs - displayedFor);
                setTimeout(() => { this[key] = false; }, remaining);
            } else {
                this[key] = false;
            }
        }
    };
}

/**
 * Wrap an async function with loading state management
 * 
 * Usage:
 *   const result = await withLoading(this, 'isLoading', async () => {
 *       return await fetch('/api/data');
 *   });
 */
export async function withLoading(context, key, asyncFn, options = {}) {
    const { delay = 300, minDisplay = 200 } = options;

    let loadingTimeout = null;
    const startTime = Date.now();

    loadingTimeout = setTimeout(() => {
        context[key] = true;
    }, delay);

    try {
        return await asyncFn();
    } finally {
        clearTimeout(loadingTimeout);
        const elapsed = Date.now() - startTime;

        if (context[key]) {
            const displayedFor = elapsed - delay;
            const remaining = Math.max(0, minDisplay - displayedFor);
            setTimeout(() => { context[key] = false; }, remaining);
        } else {
            context[key] = false;
        }
    }
}

// Default export for Alpine registration
export default { loadingState, withLoading };
