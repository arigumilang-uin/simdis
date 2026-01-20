<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'IDEAL') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Chart.js: Only loaded on pages that need it via @push('chartjs') -->
    @stack('chartjs')
    
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
             @click="close()"></div>
        
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar" :class="{ 'open': open, 'animated': animated }">
            @include('components.sidebar')
        </aside>
        
        <!-- Script to apply initial state immediately to prevent flicker -->
        <script>
            (function() {
                if (window.innerWidth >= 1024) {
                    const saved = localStorage.getItem('sidebar_open');
                    if (saved === null || saved === 'true') {
                        document.getElementById('sidebar').classList.add('open');
                    }
                }
            })();
        </script>
        
        <!-- Main Content -->
        <main class="main-content flex flex-col min-h-screen" :class="{ 'animated': animated }">
            
            <!-- Unified Sticky Header Wrapper -->
            <!-- Wraps both Navbar and Page Header to ensure they stick together as a single unit -->
            <!-- Z-index 30 ensures navbar stays below Sidebar (z-50) but above content -->
            <div class="sticky top-0 z-30 w-full transition-all duration-300">
                
                <!-- Navbar Segment -->
                <header class="navbar w-full h-16 bg-[#fcfcfc] border-b border-gray-100/80 backdrop-blur-md bg-opacity-90 relative z-20">
                    @include('components.navbar')
                </header>
                
                {{-- Page Header Segment - Check if has content --}}
                @php
                    $pageHeaderContent = trim(View::yieldContent('page-header') ?? '');
                    $hasPageHeader = !empty($pageHeaderContent) && $pageHeaderContent !== 'false' && $pageHeaderContent !== '0';
                @endphp
                
                @if($hasPageHeader)
                {{-- Page Header Segment (Always visible, no collapse) --}}
                <div class="relative z-10 bg-[#f8fafc] border-b border-gray-200/50">
                    <div class="px-1 md:px-6 pt-4 pb-4"> 
                        <!-- Flash Messages -->
                        @include('components.alerts')
                        
                        <!-- Page Header -->
                        <div>
                            @yield('page-header')
                        </div>
                    </div>
                </div>
                @else
                {{-- Dashboard mode: No header, just flash messages if any --}}
                @if(session()->has('success') || session()->has('error') || session()->has('warning') || session()->has('info') || $errors->any())
                <div class="px-0 md:px-6 pt-2 pb-1">
                    @include('components.alerts')
                </div>
                @endif
                @endif
            </div>
            
            <!-- Page Content (Scrollable Body) -->
            <div class="page-content relative bg-[#f8fafc] flex-1">
                <!-- Main Content Area -->
                <!-- px-1 on mobile: almost full width for tables -->
                <div class="px-0 md:px-3 pt-6 pb-4 flex flex-col [&>*:first-child]:mt-0"> 
                    @yield('content')
                </div>
            </div>

            
            <!-- Footer -->
            <footer class="px-6 py-4 text-center text-sm text-gray-500 border-t border-gray-100">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'IDEAL') }}. Integrated Discipline & Educational Achievement Log.</p>
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
