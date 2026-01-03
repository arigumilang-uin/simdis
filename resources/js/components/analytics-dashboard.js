export default (config) => ({
    isLoading: false,
    filters: config.filters || {},
    chartInstances: {},
    endpoint: config.endpoint || window.location.href,

    init() {
        // Menggunakan nextTick agar DOM chart canvas sudah siap
        this.$nextTick(() => {
            this.initCharts(config.charts || {});
        });

        // Setup Auto-Watch untuk semua filter
        // Apapun filter yang dipassing dari Blade (start_date, jurusan_id, dll), akan di-watch.
        Object.keys(this.filters).forEach(key => {
            this.$watch(`filters.${key}`, () => this.fetchData());
        });
    },

    async fetchData() {
        this.isLoading = true;
        
        // Build Query String dari filters
        const params = new URLSearchParams();
        Object.entries(this.filters).forEach(([key, value]) => {
            if (value !== null && value !== undefined) {
                params.append(key, value);
            }
        });

        const url = `${this.endpoint}?${params.toString()}`;

        // Update URL Browser (History API) tanpa reload
        try { window.history.pushState({}, '', url); } catch(e) {}

        try {
            const response = await fetch(url, { 
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                } 
            });

            if (response.ok) {
                const data = await response.json();

                // 1. Update HTML Containers (Stats, Table)
                // Kita cari elemen berdasarkan CONFIG key atau default ID
                if (data.stats) {
                    const el = document.getElementById('stats-container');
                    if (el) el.innerHTML = data.stats;
                }
                
                if (data.table) {
                    const el = document.getElementById('table-container');
                    if (el) el.innerHTML = data.table;
                }

                // 2. Update Charts secara dinamis
                if (data.charts) {
                    this.updateCharts(data.charts);
                }
            } else {
                console.warn('Dashboard fetch returned non-OK status');
            }
        } catch (error) {
            console.error('Dashboard Fetch Error:', error);
            // Opsional: Tampilkan notifikasi error via Alpine Global Store/Event
        } finally {
            this.isLoading = false;
        }
    },

    resetFilters() {
        // Mengembalikan ke nilai default yang dikirim dari config
        if (config.defaults) {
            Object.assign(this.filters, config.defaults);
        }
    },

    initCharts(chartConfigs) {
        // chartConfigs adalah Object: { 'chartTrend': { type: 'line', data: {...}, options: {...} } }
        Object.entries(chartConfigs).forEach(([chartId, chartConfig]) => {
            const ctx = document.getElementById(chartId);
            if (!ctx) return;

            // Merge default options dengan custom options
            const options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'bottom', 
                        labels: { 
                            usePointStyle: true, 
                            boxWidth: 8, 
                            padding: 20, 
                            font: { size: 11, family: "'Inter', sans-serif" } 
                        } 
                    } 
                },
                ...chartConfig.options 
            };

            this.chartInstances[chartId] = new window.Chart(ctx, {
                type: chartConfig.type,
                data: chartConfig.data,
                options: options
            });
        });
    },

    updateCharts(newChartData) {
        // newChartData: { 'trend': { labels: [], data: [] }, 'pelanggaran': { ... } }
        // Mapping key data server ke ID Chart instance harus konsisten.
        // Di Blade Kepsek: canvas id="chartTrend", data server key="trend"
        // Kita asumsikan key-nya matching atau kita lakukan mapping manual.
        
        // Strategi: Loop semua chart yang aktif, cek apakah ada update datanya.
        Object.keys(this.chartInstances).forEach(chartId => {
            // Kita coba match chartId (misal 'chartTrend') dengan keys di newChartData (misal 'trend')
            // Kita buat convention: chartId "chartTrend" -> data key "trend" (remove 'chart' prefix + lowercase first)
            
            let dataKey = chartId.replace(/^chart/, '');
            dataKey = dataKey.charAt(0).toLowerCase() + dataKey.slice(1);
            
            // Atau support direct key match
            const updateData = newChartData[dataKey] || newChartData[chartId];

            if (updateData && this.chartInstances[chartId]) {
                const chart = this.chartInstances[chartId];
                
                // Update Labels
                if (updateData.labels) {
                    chart.data.labels = updateData.labels;
                }

                // Update Dataset Data (Support Single Dataset mainly)
                if (updateData.data && chart.data.datasets.length > 0) {
                    chart.data.datasets[0].data = updateData.data;
                }
                
                // Support Multiple Datasets update jika struktur cocok
                if (updateData.datasets) {
                     chart.data.datasets = updateData.datasets;
                }

                chart.update();
            }
        });
    }
});
