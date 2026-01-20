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
import analyticsDashboard from "./components/analytics-dashboard";
import dataTable from "./components/data-table";

// ============================================
// ALPINE GLOBAL COMPONENTS
// ============================================

// Sidebar Toggle Component with localStorage persistence
Alpine.data("sidebar", () => ({
    open: true, // Default open
    animated: false, // Transition enabler

    init() {
        // Load saved state from localStorage (desktop only)
        if (window.innerWidth >= 1024) {
            const saved = localStorage.getItem('sidebar_open');
            this.open = saved === null ? true : saved === 'true';
        } else {
            this.open = false; // Mobile always starts closed
        }

        // Enable transitions after a tick
        this.$nextTick(() => {
            this.animated = true;

            // Restore sidebar scroll position
            this.restoreScrollPosition();
        });

        // Watch for open state changes
        this.$watch('open', (value) => {
            this.updateBodyScroll(value);
            // Save state to localStorage (desktop only)
            if (window.innerWidth >= 1024) {
                localStorage.setItem('sidebar_open', value);
            }
        });

        // Save scroll position before page unload
        window.addEventListener('beforeunload', () => {
            this.saveScrollPosition();
        });

        // Also save on link click (for SPA-like navigation)
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.addEventListener('click', (e) => {
                if (e.target.closest('a')) {
                    this.saveScrollPosition();
                }
            });
        }
    },

    // Save sidebar scroll position to sessionStorage
    saveScrollPosition() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sessionStorage.setItem('sidebar_scroll', sidebar.scrollTop);
        }
    },

    // Restore sidebar scroll position from sessionStorage
    restoreScrollPosition() {
        const sidebar = document.getElementById('sidebar');
        const savedScroll = sessionStorage.getItem('sidebar_scroll');
        if (sidebar && savedScroll) {
            sidebar.scrollTop = parseInt(savedScroll, 10);
        }
    },

    // Lock/unlock body scroll based on sidebar state (mobile only)
    updateBodyScroll(isOpen) {
        if (window.innerWidth < 1024) {
            if (isOpen) {
                document.body.dataset.scrollY = window.scrollY;
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.top = `-${window.scrollY}px`;
                document.body.style.left = '0';
                document.body.style.right = '0';
            } else {
                const scrollY = document.body.dataset.scrollY || '0';
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.left = '';
                document.body.style.right = '';
                window.scrollTo(0, parseInt(scrollY));
            }
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
    },
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

// Slide-over Drawer Component
Alpine.data("slideOver", (config = {}) => ({
    open: false,
    loading: false,
    title: config.title || "",
    size: config.size || "md", // sm, md, lg, xl, full

    show(options = {}) {
        if (options.title) this.title = options.title;
        if (options.size) this.size = options.size;
        this.open = true;
        document.body.style.overflow = "hidden";

        // Focus first input after animation
        this.$nextTick(() => {
            const firstInput = this.$el.querySelector(
                'input:not([type="hidden"]), select, textarea'
            );
            if (firstInput) firstInput.focus();
        });
    },

    hide() {
        this.open = false;
        document.body.style.overflow = "";
        this.$dispatch("slide-over-closed");
    },

    toggle() {
        if (this.open) {
            this.hide();
        } else {
            this.show();
        }
    },

    // Handle escape key
    handleEscape(e) {
        if (e.key === "Escape" && this.open) {
            this.hide();
        }
    },

    // Get width class based on size
    getWidthClass() {
        const sizes = {
            sm: "max-w-sm",
            md: "max-w-md",
            lg: "max-w-lg",
            xl: "max-w-xl",
            "2xl": "max-w-2xl",
            full: "max-w-full",
        };
        return sizes[this.size] || "max-w-md";
    },
}));

// Global Slide-over Store for triggering from anywhere
Alpine.store("slideOver", {
    component: null,

    register(component) {
        this.component = component;
    },

    show(options) {
        if (this.component) {
            this.component.show(options);
        }
    },

    hide() {
        if (this.component) {
            this.component.hide();
        }
    },
});

// Kelas Form Component (Dynamic konsentrasi loading)
// NOTE: nama_kelas and wali username are auto-generated by backend
Alpine.data("kelasForm", (config = {}) => ({
    tingkat: config.tingkat || "",
    jurusanId: config.jurusanId || "",
    konsentrasiId: config.konsentrasiId || "",
    createWali: false,

    // Dynamic konsentrasi
    konsentrasiList: [],
    loadingKonsentrasi: false,
    konsentrasiApiUrl: config.konsentrasiApiUrl || "/api/konsentrasi/by-jurusan",

    init() {
        if (this.jurusanId) {
            this.loadKonsentrasi();
        }
    },

    async loadKonsentrasi() {
        this.konsentrasiId = "";
        this.konsentrasiList = [];

        if (!this.jurusanId) return;

        this.loadingKonsentrasi = true;
        try {
            const response = await fetch(`${this.konsentrasiApiUrl}?jurusan_id=${this.jurusanId}`);
            this.konsentrasiList = await response.json();
        } catch (error) {
            console.error("Failed to load konsentrasi:", error);
        } finally {
            this.loadingKonsentrasi = false;
        }
    },

    onJurusanChange() {
        this.loadKonsentrasi();
    },
}));

// User Form Component (Centralized logic for create/edit user)
Alpine.data("userForm", (config = {}) => ({
    roleId: config.roleId || "",
    roleMap: config.roleMap || {},

    getRoleName() {
        return this.roleMap[this.roleId] || "";
    },

    needsNipNuptk() {
        const roles = ["guru", "waka kesiswaan", "waka kurikulum", "waka sarana", "operator sekolah", "wali kelas", "kaprodi", "kepala sekolah"];
        const name = this.getRoleName();
        return roles.some((r) => name.includes(r));
    },

    isWaliKelas() {
        return this.getRoleName().includes("wali kelas");
    },

    isKaprodi() {
        return this.getRoleName().includes("kaprodi");
    },

    isWaliMurid() {
        return this.getRoleName().includes("wali murid");
    },

    isDeveloper() {
        return this.getRoleName().includes("developer");
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
Alpine.data("analyticsDashboard", analyticsDashboard);
Alpine.data("dataTable", dataTable);

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
