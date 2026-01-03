{{-- Flash Alerts Component --}}

{{-- Success Alert --}}
@if(session('success'))
    <div x-data="alert(true, 5000)" x-show="visible" x-transition class="alert alert-success mb-4">
        <x-ui.icon name="check-circle" class="alert-icon" />
        <div class="alert-content">
            <p class="alert-message">{{ session('success') }}</p>
        </div>
        <button type="button" @click="dismiss()" class="ml-auto text-current opacity-50 hover:opacity-100">
            <x-ui.icon name="x" size="18" />
        </button>
    </div>
@endif

{{-- Error Alert --}}
@if(session('error'))
    <div x-data="alert(true, 8000)" x-show="visible" x-transition class="alert alert-danger mb-4">
        <x-ui.icon name="x" class="alert-icon" />
        <div class="alert-content">
            <p class="alert-message">{{ session('error') }}</p>
        </div>
        <button type="button" @click="dismiss()" class="ml-auto text-current opacity-50 hover:opacity-100">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
            </svg>
        </button>
    </div>
@endif

{{-- Warning Alert --}}
@if(session('warning'))
    <div x-data="alert(true, 6000)" x-show="visible" x-transition class="alert alert-warning mb-4">
        <x-ui.icon name="alert-triangle" class="alert-icon" />
        <div class="alert-content">
            <p class="alert-message">{{ session('warning') }}</p>
        </div>
        <button type="button" @click="dismiss()" class="ml-auto text-current opacity-50 hover:opacity-100">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
            </svg>
        </button>
    </div>
@endif

{{-- Info Alert --}}
@if(session('info'))
    <div x-data="alert(true, 5000)" x-show="visible" x-transition class="alert alert-info mb-4">
        <x-ui.icon name="info" class="alert-icon" />
        <div class="alert-content">
            <p class="alert-message">{{ session('info') }}</p>
        </div>
        <button type="button" @click="dismiss()" class="ml-auto text-current opacity-50 hover:opacity-100">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
            </svg>
        </button>
    </div>
@endif

{{-- Validation Errors --}}
@if($errors->any())
    <div x-data="alert(false)" x-show="visible" x-transition class="alert alert-danger mb-4">
        <x-ui.icon name="x" class="alert-icon" />
        <div class="alert-content">
            <p class="alert-title">Terdapat kesalahan pada form:</p>
            <ul class="mt-2 space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" @click="dismiss()" class="ml-auto text-current opacity-50 hover:opacity-100 self-start">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
            </svg>
        </button>
    </div>
@endif

{{-- Wali Created Info (Special for Siswa creation) --}}
@if(session('wali_created'))
    @php $waliData = session('wali_created'); @endphp
    <div x-data="alert(false)" x-show="visible" x-transition class="alert alert-info mb-4 !items-start">
        <x-ui.icon name="users" class="alert-icon" />
        <div class="alert-content">
            <p class="alert-title">Akun Wali Murid Telah Dibuat Otomatis</p>
            <div class="mt-3 grid grid-cols-2 gap-4 text-sm">
                <div class="bg-white/50 rounded-lg p-3 border border-current/10">
                    <p class="text-xs opacity-60 uppercase tracking-wide font-semibold">Username</p>
                    <p class="font-mono font-bold mt-1">{{ $waliData['username'] }}</p>
                </div>
                <div class="bg-white/50 rounded-lg p-3 border border-current/10">
                    <p class="text-xs opacity-60 uppercase tracking-wide font-semibold">Password</p>
                    <p class="font-mono font-bold mt-1 text-red-600">{{ $waliData['password'] }}</p>
                </div>
            </div>
            <p class="text-xs opacity-70 mt-3 italic">* Pastikan untuk menyampaikan kredensial ini dan menyarankan perubahan password.</p>
        </div>
        <button type="button" @click="dismiss()" class="ml-auto text-current opacity-50 hover:opacity-100">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
            </svg>
        </button>
    </div>
@endif
