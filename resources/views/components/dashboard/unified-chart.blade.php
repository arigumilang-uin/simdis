@props([
    'chartData' => [],
    'chartId' => 'mainChart',
    'minHeight' => '400px',
])

@php
$modes = [
    'trend' => ['icon' => 'trending-up', 'label' => 'Tren Bulanan'],
    'jenis' => ['icon' => 'pie-chart', 'label' => 'Jenis Pelanggaran'],
    'jurusan' => ['icon' => 'layers', 'label' => 'Per Jurusan'],
    'kelas' => ['icon' => 'users', 'label' => 'Per Kelas'],
];
@endphp

<div 
    {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl bg-white ring-1 ring-gray-900/5 shadow-lg']) }}
    x-data="unifiedChart({
        chartId: '{{ $chartId }}',
        initialData: {{ json_encode($chartData) }},
        modes: {{ json_encode($modes) }}
    })"
>
    {{-- Header with Mode Selector --}}
    <div class="relative z-10 px-6 py-5 border-b border-gray-100/80">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            {{-- Title --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-800" x-text="currentTitle">{{ $chartData['title'] ?? 'Statistik' }}</h3>
                <p class="text-sm text-gray-500 mt-0.5" x-text="currentSubtitle">{{ $chartData['subtitle'] ?? '' }}</p>
            </div>
            
            {{-- Mode Selector Pills --}}
            <div class="flex items-center gap-1 p-1 bg-gray-100 rounded-xl">
                @foreach($modes as $key => $mode)
                <button 
                    type="button"
                    @click="switchMode('{{ $key }}')"
                    :class="{
                        'bg-white shadow-sm text-gray-800': activeMode === '{{ $key }}',
                        'text-gray-500 hover:text-gray-700': activeMode !== '{{ $key }}'
                    }"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200"
                >
                    <x-ui.icon name="{{ $mode['icon'] }}" :size="16" />
                    <span class="hidden md:inline">{{ $mode['label'] }}</span>
                </button>
                @endforeach
            </div>
        </div>
    </div>
    
    {{-- Chart Container --}}
    <div class="relative p-6" style="min-height: {{ $minHeight }}">
        {{-- Loading Overlay --}}
        <div 
            x-show="isLoading" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-20"
            style="display: none;"
        >
            <div class="flex flex-col items-center gap-3">
                <div class="relative">
                    <div class="w-12 h-12 border-4 border-primary-200 rounded-full"></div>
                    <div class="absolute top-0 left-0 w-12 h-12 border-4 border-primary-600 rounded-full border-t-transparent animate-spin"></div>
                </div>
                <p class="text-sm text-gray-600 font-medium">Memuat data...</p>
            </div>
        </div>
        
        {{-- Chart Canvas --}}
        <div class="w-full h-full" style="min-height: calc({{ $minHeight }} - 3rem);">
            <canvas id="{{ $chartId }}"></canvas>
        </div>
    </div>
    
    {{-- Footer with Quick Stats --}}
    <div class="relative z-10 px-6 py-4 bg-gradient-to-b from-gray-50/50 to-white border-t border-gray-100/80">
        <div class="flex items-center justify-between text-sm">
            <div class="flex items-center gap-4">
                <span class="text-gray-500">Total Data:</span>
                <span class="font-semibold text-gray-800" x-text="totalData">0</span>
            </div>
            <div class="flex items-center gap-2 text-gray-400">
                <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                <span class="text-xs">Live Update</span>
            </div>
        </div>
    </div>
</div>

@pushOnce('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('unifiedChart', (config) => ({
        chartId: config.chartId,
        activeMode: '{{ $chartData['type'] ?? 'trend' }}' === 'line' ? 'trend' : 
                    '{{ $chartData['type'] ?? 'trend' }}' === 'doughnut' ? 'jenis' : 'trend',
        isLoading: false,
        chartInstance: null,
        currentTitle: config.initialData.title || 'Statistik',
        currentSubtitle: config.initialData.subtitle || '',
        totalData: 0,
        
        init() {
            this.$nextTick(() => {
                this.initChart(config.initialData);
            });
            
            // Calculate initial total
            if (config.initialData.data) {
                this.totalData = config.initialData.data.reduce((a, b) => a + b, 0);
            }
        },
        
        initChart(data) {
            const canvas = document.getElementById(this.chartId);
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            
            // Build dataset based on type
            let chartData = {
                labels: data.labels || [],
                datasets: [{
                    label: data.title || 'Data',
                    data: data.data || [],
                    ...this.getDatasetStyle(data.type || 'line', ctx)
                }]
            };
            
            // Chart options based on type
            let options = {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 500 },
                plugins: {
                    legend: { display: data.type === 'doughnut', position: 'bottom' },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 14, weight: '600' },
                        bodyFont: { size: 13 },
                    }
                },
                ...this.getChartOptions(data.type || 'line'),
                ...(data.options || {})
            };
            
            if (this.chartInstance) {
                this.chartInstance.destroy();
            }
            
            this.chartInstance = new Chart(ctx, {
                type: data.type || 'line',
                data: chartData,
                options: options
            });
        },
        
        getDatasetStyle(type, ctx) {
            const colors = ['#06b6d4', '#8b5cf6', '#f59e0b', '#ef4444', '#10b981', '#ec4899', '#6366f1', '#14b8a6', '#f97316', '#84cc16'];
            
            switch(type) {
                case 'line':
                    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
                    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');
                    return {
                        borderColor: '#10b981',
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderWidth: 3,
                    };
                case 'doughnut':
                case 'pie':
                    return {
                        backgroundColor: colors,
                        borderWidth: 0,
                        hoverOffset: 8,
                    };
                case 'bar':
                    return {
                        backgroundColor: '#10b981',
                        borderRadius: 8,
                        barThickness: 24,
                    };
                default:
                    return { backgroundColor: colors[0] };
            }
        },
        
        getChartOptions(type) {
            switch(type) {
                case 'line':
                case 'bar':
                    return {
                        scales: {
                            x: { grid: { display: false }, border: { display: false } },
                            y: { 
                                beginAtZero: true, 
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                border: { display: false }
                            }
                        }
                    };
                case 'doughnut':
                    return { cutout: '70%' };
                default:
                    return {};
            }
        },
        
        async switchMode(mode) {
            if (this.activeMode === mode || this.isLoading) return;
            
            this.isLoading = true;
            this.activeMode = mode;
            
            // Build URL with current filters from parent analyticsDashboard
            const params = new URLSearchParams(window.location.search);
            params.set('chart_mode', mode);
            
            try {
                const response = await fetch(`${window.location.pathname}?${params.toString()}`, {
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.charts && data.charts.mainChart) {
                        const chartData = data.charts.mainChart;
                        this.currentTitle = chartData.title;
                        this.currentSubtitle = chartData.subtitle;
                        this.totalData = chartData.data ? chartData.data.reduce((a, b) => a + b, 0) : 0;
                        this.initChart(chartData);
                    }
                }
            } catch (error) {
                console.error('Error switching chart mode:', error);
            } finally {
                setTimeout(() => { this.isLoading = false; }, 200);
            }
        }
    }));
});
</script>
@endPushOnce
