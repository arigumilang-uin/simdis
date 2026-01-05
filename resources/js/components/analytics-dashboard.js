/**
 * Analytics Dashboard Component
 * Modern Chart.js integration with Alpine.js
 *
 * Features:
 * - Auto-watch filters
 * - AJAX data refresh
 * - Smooth chart animations
 * - Beautiful tooltips
 * - Gradient fills
 */

// Modern color palettes
const CHART_COLORS = {
    primary: ["#10b981", "#059669", "#047857", "#065f46", "#064e3b"],
    vibrant: [
        "#06b6d4", // cyan
        "#8b5cf6", // violet
        "#f59e0b", // amber
        "#ef4444", // red
        "#10b981", // emerald
        "#ec4899", // pink
        "#6366f1", // indigo
        "#14b8a6", // teal
        "#f97316", // orange
        "#84cc16", // lime
    ],
    gradient: {
        emerald: ["rgba(16, 185, 129, 0.8)", "rgba(5, 150, 105, 0.1)"],
        blue: ["rgba(59, 130, 246, 0.8)", "rgba(59, 130, 246, 0.1)"],
        violet: ["rgba(139, 92, 246, 0.8)", "rgba(139, 92, 246, 0.1)"],
        rose: ["rgba(244, 63, 94, 0.8)", "rgba(244, 63, 94, 0.1)"],
        amber: ["rgba(245, 158, 11, 0.8)", "rgba(245, 158, 11, 0.1)"],
    },
};

// Modern default chart options
const DEFAULT_OPTIONS = {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
        duration: 750,
        easing: "easeOutQuart",
    },
    interaction: {
        mode: "index",
        intersect: false,
    },
    plugins: {
        legend: {
            position: "bottom",
            align: "center",
            labels: {
                usePointStyle: true,
                pointStyle: "circle",
                boxWidth: 8,
                boxHeight: 8,
                padding: 20,
                font: {
                    size: 12,
                    family: "'Inter', -apple-system, sans-serif",
                    weight: "500",
                },
                color: "#64748b",
            },
        },
        tooltip: {
            enabled: true,
            backgroundColor: "rgba(15, 23, 42, 0.9)",
            titleColor: "#fff",
            bodyColor: "#e2e8f0",
            bodyFont: {
                size: 13,
                family: "'Inter', sans-serif",
            },
            titleFont: {
                size: 14,
                weight: "600",
                family: "'Inter', sans-serif",
            },
            padding: 14,
            cornerRadius: 12,
            boxPadding: 6,
            usePointStyle: true,
            borderColor: "rgba(255, 255, 255, 0.1)",
            borderWidth: 1,
            displayColors: true,
            callbacks: {
                label: function (context) {
                    let label = context.dataset.label || "";
                    if (label) {
                        label += ": ";
                    }
                    if (context.parsed.y !== null) {
                        label += new Intl.NumberFormat("id-ID").format(
                            context.parsed.y
                        );
                    } else if (context.parsed !== null) {
                        label += new Intl.NumberFormat("id-ID").format(
                            context.parsed
                        );
                    }
                    return label;
                },
            },
        },
    },
};

// Chart type specific defaults
const TYPE_DEFAULTS = {
    line: {
        elements: {
            line: {
                tension: 0.4,
                borderWidth: 3,
                borderCapStyle: "round",
            },
            point: {
                radius: 0,
                hoverRadius: 6,
                hoverBorderWidth: 3,
                backgroundColor: "#fff",
            },
        },
        scales: {
            x: {
                grid: {
                    display: false,
                },
                ticks: {
                    font: { size: 11, family: "'Inter', sans-serif" },
                    color: "#94a3b8",
                },
                border: {
                    display: false,
                },
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: "rgba(148, 163, 184, 0.1)",
                    drawBorder: false,
                },
                ticks: {
                    font: { size: 11, family: "'Inter', sans-serif" },
                    color: "#94a3b8",
                    padding: 10,
                },
                border: {
                    display: false,
                    dash: [4, 4],
                },
            },
        },
    },
    bar: {
        elements: {
            bar: {
                borderRadius: 8,
                borderSkipped: false,
            },
        },
        scales: {
            x: {
                grid: {
                    display: false,
                },
                ticks: {
                    font: { size: 11, family: "'Inter', sans-serif" },
                    color: "#94a3b8",
                },
                border: {
                    display: false,
                },
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: "rgba(148, 163, 184, 0.1)",
                },
                ticks: {
                    font: { size: 11, family: "'Inter', sans-serif" },
                    color: "#94a3b8",
                    padding: 10,
                },
                border: {
                    display: false,
                },
            },
        },
    },
    doughnut: {
        cutout: "75%",
        plugins: {
            legend: {
                position: "bottom",
            },
        },
        elements: {
            arc: {
                borderWidth: 0,
                hoverOffset: 8,
            },
        },
    },
    pie: {
        plugins: {
            legend: {
                position: "bottom",
            },
        },
        elements: {
            arc: {
                borderWidth: 2,
                borderColor: "#fff",
                hoverOffset: 8,
            },
        },
    },
};

// Helper to create gradient
function createGradient(ctx, colorKey = "emerald") {
    const gradient = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
    const colors =
        CHART_COLORS.gradient[colorKey] || CHART_COLORS.gradient.emerald;
    gradient.addColorStop(0, colors[0]);
    gradient.addColorStop(1, colors[1]);
    return gradient;
}

// Deep merge utility
function deepMerge(target, source) {
    const output = { ...target };
    for (const key in source) {
        if (
            source[key] instanceof Object &&
            key in target &&
            target[key] instanceof Object
        ) {
            output[key] = deepMerge(target[key], source[key]);
        } else {
            output[key] = source[key];
        }
    }
    return output;
}

export default (config) => ({
    isLoading: false,
    filters: config.filters || {},
    chartInstances: {},
    endpoint: config.endpoint || window.location.href,

    init() {
        // Initialize charts after DOM is ready
        this.$nextTick(() => {
            this.initCharts(config.charts || {});
        });

        // Auto-watch all filters
        Object.keys(this.filters).forEach((key) => {
            this.$watch(`filters.${key}`, () => this.fetchData());
        });
    },

    async fetchData() {
        this.isLoading = true;

        const params = new URLSearchParams();
        Object.entries(this.filters).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== "") {
                params.append(key, value);
            }
        });

        const url = `${this.endpoint}?${params.toString()}`;

        // Update browser URL
        try {
            window.history.pushState({}, "", url);
        } catch (e) {}

        try {
            const response = await fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
            });

            if (response.ok) {
                const data = await response.json();

                // Update HTML containers
                if (data.stats) {
                    const el = document.getElementById("stats-container");
                    if (el) el.innerHTML = data.stats;
                }

                if (data.table) {
                    const el = document.getElementById("table-container");
                    if (el) el.innerHTML = data.table;
                }

                // Update charts with animation
                if (data.charts) {
                    this.updateCharts(data.charts);
                }
            }
        } catch (error) {
            console.error("Dashboard Fetch Error:", error);
        } finally {
            // Small delay for smoother UX
            setTimeout(() => {
                this.isLoading = false;
            }, 300);
        }
    },

    resetFilters() {
        if (config.defaults) {
            Object.assign(this.filters, config.defaults);
        }
    },

    initCharts(chartConfigs) {
        Object.entries(chartConfigs).forEach(([chartId, chartConfig]) => {
            const canvas = document.getElementById(chartId);
            if (!canvas) return;

            const ctx = canvas.getContext("2d");
            const chartType = chartConfig.type || "bar";

            // Apply modern colors if not specified
            if (chartConfig.data && chartConfig.data.datasets) {
                chartConfig.data.datasets = chartConfig.data.datasets.map(
                    (dataset, index) => {
                        // For line charts, add gradient fill
                        if (chartType === "line" && !dataset.backgroundColor) {
                            const gradientKeys = Object.keys(
                                CHART_COLORS.gradient
                            );
                            const gradientKey =
                                gradientKeys[index % gradientKeys.length];
                            return {
                                ...dataset,
                                borderColor:
                                    dataset.borderColor ||
                                    CHART_COLORS.vibrant[
                                        index % CHART_COLORS.vibrant.length
                                    ],
                                backgroundColor: createGradient(
                                    ctx,
                                    gradientKey
                                ),
                                fill: true,
                            };
                        }

                        // For doughnut/pie, ensure vibrant colors
                        if (
                            (chartType === "doughnut" || chartType === "pie") &&
                            !dataset.backgroundColor
                        ) {
                            return {
                                ...dataset,
                                backgroundColor: CHART_COLORS.vibrant,
                            };
                        }

                        // For bar charts
                        if (chartType === "bar" && !dataset.backgroundColor) {
                            return {
                                ...dataset,
                                backgroundColor:
                                    dataset.backgroundColor ||
                                    CHART_COLORS.vibrant[
                                        index % CHART_COLORS.vibrant.length
                                    ],
                            };
                        }

                        return dataset;
                    }
                );
            }

            // Merge options: DEFAULT -> TYPE_DEFAULTS -> custom
            const typeDefaults = TYPE_DEFAULTS[chartType] || {};
            const mergedOptions = deepMerge(
                deepMerge(DEFAULT_OPTIONS, typeDefaults),
                chartConfig.options || {}
            );

            this.chartInstances[chartId] = new window.Chart(ctx, {
                type: chartType,
                data: chartConfig.data,
                options: mergedOptions,
            });
        });
    },

    updateCharts(newChartData) {
        Object.keys(this.chartInstances).forEach((chartId) => {
            let dataKey = chartId.replace(/^chart/, "");
            dataKey = dataKey.charAt(0).toLowerCase() + dataKey.slice(1);

            const updateData = newChartData[dataKey] || newChartData[chartId];

            if (updateData && this.chartInstances[chartId]) {
                const chart = this.chartInstances[chartId];

                if (updateData.labels) {
                    chart.data.labels = updateData.labels;
                }

                if (updateData.data && chart.data.datasets.length > 0) {
                    chart.data.datasets[0].data = updateData.data;
                }

                if (updateData.datasets) {
                    chart.data.datasets = updateData.datasets;
                }

                // Animate the update
                chart.update("active");
            }
        });
    },
});
