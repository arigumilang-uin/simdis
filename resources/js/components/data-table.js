export default (config) => ({
    isLoading: false,
    endpoint: config.endpoint,
    containerId: config.containerId || 'table-container',
    // Filters object, keys match the query params
    filters: config.filters || {},
    // Bulk Selection State
    selectionMode: false,
    selectAll: false,
    selected: [],

    // Spread any custom state from config
    ...(config.customState || {}),

    // Dependent dropdown config
    dependentDropdowns: config.dependentDropdowns || [],
    // Example: dependentDropdowns: [{ trigger: 'jurusan_id', target: 'kelas_id', url: '/api/kelas-by-jurusan', paramKey: 'jurusan_id' }]
    // Each entry: when trigger changes, fetch from url?paramKey=value, update targetListName array

    init() {
        // Filters to exclude from auto-watch (handled manually, e.g., with @change)
        const excludeFromWatch = config.excludeFromWatch || [];

        // Setup Auto-Watch untuk semua filter (kecuali yang di-exclude)
        Object.keys(this.filters).forEach(key => {
            if (!excludeFromWatch.includes(key)) {
                this.$watch(`filters.${key}`, () => this.fetchData());
            }
        });

        // Watch selection mode to clear selection
        this.$watch('selectionMode', (value) => {
            if (!value) {
                this.selected = [];
                this.selectAll = false;
            }
        });

        // Pagination Click Listener (Event Delegation)
        const container = document.getElementById(this.containerId);
        if (container) {
            container.addEventListener('click', (e) => {
                // Support Laravel Pagination Links standard markup
                const link = e.target.closest('.pagination a') || e.target.closest('a.page-link');
                if (link) {
                    e.preventDefault();
                    this.fetchData(link.href);
                }
            });
        }

        // Browser Back Button Support
        window.addEventListener('popstate', (event) => {
            this.fetchData(window.location.href, false);
        });

        // Call custom init if provided
        if (typeof config.onInit === 'function') {
            config.onInit.call(this);
        }
    },

    async fetchData(url = null, updatePushState = true) {
        // Loading delay configuration (prevents flicker for fast operations)
        const LOADING_DELAY_MS = 300;  // Show loading indicator after this delay
        const MIN_DISPLAY_MS = 200;    // Minimum time to show loading once visible

        let loadingTimeout = null;
        const startTime = Date.now();

        // Delayed loading indicator - only show if request takes > 300ms
        loadingTimeout = setTimeout(() => {
            this.isLoading = true;
        }, LOADING_DELAY_MS);

        let fetchUrl = url;

        // Jika URL kosong, construct dari endpoint + filters
        if (!fetchUrl) {
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([key, value]) => {
                if (value !== null && value !== '' && value !== undefined) {
                    params.append(key, value);
                }
            });

            // Clean URL for Browser History (Tanpa render_partial)
            const cleanUrl = `${this.endpoint}?${params.toString()}`;

            if (updatePushState) {
                try { window.history.pushState({}, '', cleanUrl); } catch (e) { }
            }

            // URL for Fetching (With render_partial)
            params.append('render_partial', '1');
            fetchUrl = `${this.endpoint}?${params.toString()}`;
        } else {
            // Jika URL dari pagination, tambahkan render_partial
            const urlObj = new URL(url);
            urlObj.searchParams.append('render_partial', '1');
            fetchUrl = urlObj.toString();

            if (updatePushState) {
                try { window.history.pushState({}, '', url); } catch (e) { }
            }
        }

        try {
            const response = await fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html' // Expect HTML Fragment
                }
            });

            if (response.ok) {
                const html = await response.text();
                const container = document.getElementById(this.containerId);
                if (container) {
                    container.innerHTML = html;

                    // Re-initialize any selection state if needed
                    // (Checkbox di table baru belum di-select)
                    if (this.selectAll) {
                        // Jika selectAll aktif, kita mungkin perlu re-select checkbox baru
                        // Tapi logic sederhana: reset selection on page change
                        this.selected = [];
                        this.selectAll = false;
                    }
                }
            } else {
                console.error('Data table fetch failed:', response.status);
            }
        } catch (error) {
            console.error('Data table error:', error);
        } finally {
            clearTimeout(loadingTimeout);
            const elapsed = Date.now() - startTime;

            // If loading was shown, ensure minimum display time for smooth UX
            if (this.isLoading) {
                const displayedFor = elapsed - LOADING_DELAY_MS;
                const remainingDisplay = Math.max(0, MIN_DISPLAY_MS - displayedFor);
                setTimeout(() => { this.isLoading = false; }, remainingDisplay);
            } else {
                this.isLoading = false;
            }
        }
    },

    resetFilters() {
        Object.keys(this.filters).forEach(key => {
            this.filters[key] = '';
        });

        // Call custom reset if provided
        if (typeof config.onReset === 'function') {
            config.onReset.call(this);
        }
    },

    toggleSelectionMode() {
        this.selectionMode = !this.selectionMode;
    },

    toggleSelectAll() {
        // Cari checkbox di dalam container
        const container = document.getElementById(this.containerId);
        if (!container) return;

        const checkboxes = container.querySelectorAll('input[type="checkbox"][value]');
        // Asumsi checkbox value adalah ID row

        if (this.selectAll) {
            this.selected = Array.from(checkboxes).map(cb => cb.value);
        } else {
            this.selected = [];
        }
    },

    // Helper untuk mengecek apakah ID tertentu terpilih (reaktif)
    isSelected(id) {
        return this.selected.includes(String(id));
    },

    // Helper untuk dependent dropdown loading (fetch options dari API)
    async loadDependentOptions(url, targetListProperty) {
        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (response.ok) {
                this[targetListProperty] = await response.json();
            }
        } catch (error) {
            console.error('Error loading dependent options:', error);
        }
    }
});

