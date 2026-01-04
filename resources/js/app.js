import "./bootstrap";
import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";

// Register Alpine plugins
Alpine.plugin(collapse);

// Chart.js is loaded via CDN in layout.blade.php (window.Chart available globally)

// Initialize Alpine.js
window.Alpine = Alpine;

// ============================================
// DASHBOARD COMPONENTS (Centralized Logic)
// ============================================
import analyticsDashboard from './components/analytics-dashboard';
import dataTable from './components/data-table';

// ============================================
// ALPINE GLOBAL COMPONENTS
// ============================================

// Sidebar Toggle Component
Alpine.data("sidebar", () => ({
    open: false,

    init() {
        // Check screen size on init
        this.checkScreenSize();
        window.addEventListener("resize", () => this.checkScreenSize());
    },

    checkScreenSize() {
        if (window.innerWidth >= 1024) {
            this.open = true;
        } else {
            this.open = false;
        }
    },

    toggle() {
        this.open = !this.open;
    },

    close() {
        if (window.innerWidth < 1024) {
            this.open = false;
        }
    },
}));

// Global Layout Store
Alpine.store("layout", {
    focusMode: false,
    
    toggleFocusMode() {
        this.focusMode = !this.focusMode;
    }
});

// Dropdown Component
Alpine.data("dropdown", () => ({
    open: false,

    toggle() {
        this.open = !this.open;
    },

    close() {
        this.open = false;
    },
}));

// Modal Component
Alpine.data("modal", (initialState = false) => ({
    open: initialState,

    show() {
        this.open = true;
        document.body.style.overflow = "hidden";
    },

    hide() {
        this.open = false;
        document.body.style.overflow = "";
    },

    toggle() {
        if (this.open) {
            this.hide();
        } else {
            this.show();
        }
    },
}));

// Alert/Toast Component
Alpine.data("alert", (autoClose = true, duration = 5000) => ({
    visible: true,

    init() {
        if (autoClose) {
            setTimeout(() => {
                this.dismiss();
            }, duration);
        }
    },

    dismiss() {
        this.visible = false;
    },
}));

// Form Validation Component
Alpine.data("form", () => ({
    loading: false,
    errors: {},

    async submit(event) {
        event.preventDefault();
        this.loading = true;
        this.errors = {};

        try {
            const form = event.target;
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: form.method || "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    Accept: "application/json",
                },
                body: formData,
            });

            if (!response.ok) {
                const data = await response.json();
                if (data.errors) {
                    this.errors = data.errors;
                }
                return false;
            }

            // Success - redirect or show message
            const data = await response.json();
            if (data.redirect) {
                window.location.href = data.redirect;
            }

            return true;
        } catch (error) {
            console.error("Form submission error:", error);
            return false;
        } finally {
            this.loading = false;
        }
    },

    hasError(field) {
        return this.errors[field] !== undefined;
    },

    getError(field) {
        return this.errors[field] ? this.errors[field][0] : "";
    },

    clearError(field) {
        delete this.errors[field];
    },
}));

// Data Table Component
Alpine.data("dataTable", () => ({
    search: "",
    sortColumn: "",
    sortDirection: "asc",
    selectedRows: [],
    selectAll: false,

    sort(column) {
        if (this.sortColumn === column) {
            this.sortDirection = this.sortDirection === "asc" ? "desc" : "asc";
        } else {
            this.sortColumn = column;
            this.sortDirection = "asc";
        }
    },

    toggleSelectAll(rows) {
        if (this.selectAll) {
            this.selectedRows = rows.map((row) => row.id);
        } else {
            this.selectedRows = [];
        }
    },

    toggleRow(id) {
        const index = this.selectedRows.indexOf(id);
        if (index === -1) {
            this.selectedRows.push(id);
        } else {
            this.selectedRows.splice(index, 1);
        }
    },

    isSelected(id) {
        return this.selectedRows.includes(id);
    },

    get hasSelected() {
        return this.selectedRows.length > 0;
    },

    get selectedCount() {
        return this.selectedRows.length;
    },
}));

// Tabs Component
Alpine.data("tabs", (defaultTab = "") => ({
    activeTab: defaultTab,

    init() {
        // Set first tab as default if not specified
        if (!this.activeTab) {
            const firstTab = this.$el.querySelector("[data-tab]");
            if (firstTab) {
                this.activeTab = firstTab.dataset.tab;
            }
        }
    },

    setTab(tab) {
        this.activeTab = tab;
    },

    isActive(tab) {
        return this.activeTab === tab;
    },
}));

// Confirmation Dialog
Alpine.data("confirm", () => ({
    open: false,
    title: "",
    message: "",
    confirmText: "Konfirmasi",
    cancelText: "Batal",
    onConfirm: null,
    variant: "danger", // danger, warning, info

    show({ title, message, confirmText, cancelText, onConfirm, variant }) {
        this.title = title || "Konfirmasi";
        this.message = message || "Apakah Anda yakin?";
        this.confirmText = confirmText || "Konfirmasi";
        this.cancelText = cancelText || "Batal";
        this.onConfirm = onConfirm;
        this.variant = variant || "danger";
        this.open = true;
        document.body.style.overflow = "hidden";
    },

    hide() {
        this.open = false;
        this.onConfirm = null;
        document.body.style.overflow = "";
    },

    confirm() {
        if (typeof this.onConfirm === "function") {
            this.onConfirm();
        }
        this.hide();
    },
}));

// Number Formatting
Alpine.data("numberFormat", () => ({
    format(number) {
        return new Intl.NumberFormat("id-ID").format(number);
    },

    currency(number) {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(number);
    },
}));

// ============================================
// GLOBAL UTILITIES
// ============================================

// Format date to Indonesian locale
window.formatDate = (dateString, options = {}) => {
    const defaultOptions = {
        day: "numeric",
        month: "long",
        year: "numeric",
    };
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", { ...defaultOptions, ...options });
};

// Format relative time
window.timeAgo = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);

    const intervals = {
        tahun: 31536000,
        bulan: 2592000,
        minggu: 604800,
        hari: 86400,
        jam: 3600,
        menit: 60,
        detik: 1,
    };

    for (const [unit, secondsInUnit] of Object.entries(intervals)) {
        const interval = Math.floor(seconds / secondsInUnit);
        if (interval >= 1) {
            return `${interval} ${unit} yang lalu`;
        }
    }

    return "Baru saja";
};

// Debounce function
window.debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

// Copy to clipboard
window.copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch (err) {
        console.error("Failed to copy text: ", err);
        return false;
    }
};

// Register Dashboard Components
Alpine.data('analyticsDashboard', analyticsDashboard);
Alpine.data('dataTable', dataTable);

// ============================================
// START ALPINE
// ============================================
Alpine.start();

// ============================================
// CSRF TOKEN SETUP FOR FETCH
// ============================================
const token = document.querySelector('meta[name="csrf-token"]')?.content;
if (token) {
    window.csrfToken = token;
}
