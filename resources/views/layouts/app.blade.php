<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'SIMDIS') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js (CDN - globally cached, better for shared hosting) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js" defer></script>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
    
    <style>
        /* Fix Sticky Navbar Issue */
        .main-content, .page-content {
            overflow: visible !important; /* Allow sticky to work properly */
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('layout', {
                focusMode: false,
                toggleFocusMode() {
                    this.focusMode = !this.focusMode;
                }
            });
        });
    </script>
</head>
<body x-data="sidebar" class="antialiased">
    
    <div class="app-layout">
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" 
             :class="{ 'active': open }" 
             @click="close()"
             x-show="open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:leave="transition ease-in duration-200"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'open': open }">
            @include('components.sidebar')
        </aside>
        
        <!-- Main Content -->
        <main class="main-content flex flex-col min-h-screen">
            
            <!-- Unified Sticky Header Wrapper -->
            <!-- Wraps both Navbar and Page Header to ensure they stick together as a single unit -->
            <!-- Z-index 30 ensures navbar stays below Sidebar (z-50) but above content -->
            <div class="sticky top-0 z-30 w-full transition-all duration-300">
                
                <!-- Navbar Segment -->
                <header class="navbar w-full h-16 bg-[#fcfcfc] border-b border-gray-100/80 backdrop-blur-md bg-opacity-90 relative z-20">
                    @include('components.navbar')
                </header>
                
                <!-- Page Header Segment (Collapsible) -->
                <div class="relative z-10 cursor-pointer hover:bg-gray-50/30 transition-colors"
                     :class="$store.layout.focusMode ? 'pointer-events-none' : 'bg-[#f8fafc]/95 backdrop-blur-md shadow-sm border-b border-gray-200/50'"
                     @click="$store.layout.toggleFocusMode()"
                     title="Klik untuk menyembunyikan header">
                    
                    <!-- Collapsible Area -->
                    <div x-show="!$store.layout.focusMode" x-collapse.duration.300ms 
                         class="pointer-events-auto relative">
                         
                        <!-- Ultra compact padding: px-1 on mobile for maximum table width -->
                        <div class="px-0 md:px-6 pt-2 pb-3"> 
                            <!-- Flash Messages -->
                            @include('components.alerts')
                            
                            <!-- Page Header -->
                            @hasSection('page-header')
                                <div>
                                    @yield('page-header')
                                </div>
                            @endif
                        </div>

                         <!-- Visual Handle (Restored) -->
                         <div class="absolute bottom-1 left-0 w-full flex justify-center opacity-40">
                            <div class="h-1 w-12 rounded-full bg-gray-300"></div>
                        </div>
                    </div>
                </div>

                <!-- Expand Trigger -->
                <div x-show="$store.layout.focusMode" 
                     style="display: none;"
                     class="absolute top-16 left-0 w-full flex justify-center pointer-events-auto z-50"
                     x-transition:enter="transition ease-out duration-300 delay-100"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <button @click="$store.layout.toggleFocusMode()" 
                            class="bg-white border-x border-b border-gray-200/80 shadow-sm rounded-b-full px-5 py-0.5 flex items-center justify-center group hover:shadow-md hover:border-primary-100 transition-all cursor-pointer h-5"
                            title="Tampilkan Header">
                        <x-ui.icon name="chevron-down" size="14" class="text-gray-400 group-hover:text-primary-500" />
                    </button>
                </div>
            </div>
            
            <!-- Page Content (Scrollable Body) -->
            <div class="page-content relative bg-[#f8fafc] flex-1">
                <!-- Main Content Area -->
                <!-- px-1 on mobile: almost full width for tables -->
                <div class="px-1 md:px-6 pt-0 pb-4 flex flex-col [&>*:first-child]:mt-0"> 
                    @yield('content')
                </div>
            </div>

            
            <!-- Footer -->
            <footer class="px-6 py-4 text-center text-sm text-gray-500 border-t border-gray-100">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'SIMDIS') }}. Sistem Informasi Manajemen Disiplin Siswa.</p>
            </footer>
        </main>
    </div>
    
    <!-- Global Confirmation Modal -->
    <div x-data="confirm" x-cloak>
        <div class="modal-backdrop" :class="{ 'active': open }" @keydown.escape.window="hide()">
            <div class="modal" @click.away="hide()">
                <div class="modal-header">
                    <h3 class="modal-title" x-text="title"></h3>
                    <button type="button" class="modal-close" @click="hide()">
                        <x-ui.icon name="x" size="20" />
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-gray-600" x-text="message"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="hide()" x-text="cancelText"></button>
                    <button type="button" 
                            class="btn"
                            :class="{
                                'btn-danger': variant === 'danger',
                                'btn-warning': variant === 'warning',
                                'btn-primary': variant === 'info'
                            }"
                            @click="confirm()" 
                            x-text="confirmText"></button>
                </div>
            </div>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>
