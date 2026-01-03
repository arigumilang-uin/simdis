<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ school_name() ?? 'SMK Negeri 1 Lubuk Dalam' }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Gunakan Vite untuk asset management yang terpusat -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .login-split-bg {
            background: linear-gradient(135deg, var(--color-primary-900) 0%, var(--color-primary-800) 100%);
        }
        .bg-pattern {
            background-image: radial-gradient(var(--color-primary-100) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-white text-slate-800 h-screen overflow-hidden">

    <div class="flex h-full w-full">
        
        <!-- Left Side: Branding (Menggunakan warna dari app.css) -->
        <div class="hidden lg:flex w-1/2 login-split-bg relative items-center justify-center overflow-hidden">
            
            <div class="absolute inset-0 bg-gradient-to-br from-primary-600 to-primary-900 opacity-90"></div>
            <!-- Decorative Blobs using Primary Colors -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float"></div>
            <div class="absolute top-1/2 -right-24 w-72 h-72 bg-accent-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float" style="animation-delay: 2s"></div>

            <div class="relative z-10 text-center px-12">
                <div class="mb-8 inline-flex p-4 bg-white/10 backdrop-blur-md rounded-3xl border border-white/20 shadow-2xl">
                    <picture>
                        <source srcset="{{ asset('assets/images/logo_smk.webp') }}" type="image/webp">
                        <img src="{{ asset('assets/images/logo_smk.png') }}" alt="Logo SMK" class="w-32 h-32 object-contain drop-shadow-md">
                    </picture>
                </div>
                
                <h2 class="text-4xl font-bold text-white mb-4 tracking-tight uppercase">Sistem Kedisiplinan</h2>
                <div class="w-20 h-1.5 bg-primary-400 mx-auto mb-6 rounded-full"></div>
                <p class="text-primary-100 text-lg leading-relaxed font-medium">
                    {{ school_name() ?? 'SMK Negeri 1 Lubuk Dalam' }}.<br>
                    Mewujudkan lingkungan sekolah yang tertib, disiplin, dan berkarakter melalui monitoring digital.
                </p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white bg-pattern relative">
            
            <div class="w-full max-w-md animate-fade-in relative z-10">
                
                <div class="text-center lg:text-left mb-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-600 text-xs font-bold uppercase tracking-wider mb-4 border border-primary-100">
                        ✨ Selamat Datang Kembali
                    </div>
                    <h1 class="text-3xl font-bold text-slate-900 mb-2 tracking-tight">Login Akun</h1>
                    <p class="text-slate-500">Silakan masukkan kredensial Anda untuk masuk.</p>
                </div>

                @if($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3">
                        <x-ui.icon name="alert-circle" size="20" class="text-red-600 mt-0.5 shrink-0" />
                        <div class="text-sm text-red-700">
                            <strong>Akses Ditolak!</strong>
                            <ul class="mt-1 list-disc list-inside opacity-80">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-5" x-data="{ showPassword: false }">
                    @csrf

                    <div>
                        <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">Username / NIP / NUPTK / No. HP</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary-500 transition-colors">
                                <x-ui.icon name="user" size="20" />
                            </div>
                            <!-- Gunakan class semantic 'form-input' jika ada, atau utility class yang menggunakan var(--color-primary-500) -->
                            <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus 
                                class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all font-medium"
                                placeholder="Masukkan kredensial Anda">
                        </div>
                        <p class="mt-1.5 text-xs text-slate-400">Anda bisa login dengan username, NIP, NUPTK, atau nomor HP.</p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary-500 transition-colors">
                                <x-ui.icon name="lock" size="20" />
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required 
                                class="w-full pl-12 pr-12 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-300 focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all font-medium"
                                placeholder="••••••••">
                            
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-primary-600 transition-colors cursor-pointer">
                                <x-ui.icon name="eye" size="20" x-show="!showPassword" />
                                <x-ui.icon name="eye-off" size="20" x-show="showPassword" />
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded cursor-pointer transition-colors">
                        <label for="remember" class="ml-2 block text-sm text-slate-600 cursor-pointer select-none">
                            Ingat saya di perangkat ini
                        </label>
                    </div>

                    <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 transform transition-all hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 flex items-center justify-center gap-2">
                        Masuk Sekarang
                        <x-ui.icon name="arrow-right" size="20" />
                    </button>

                </form>

                <p class="mt-8 text-center text-sm text-slate-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'SIMDIS') }} - SMK Negeri 1 Lubuk Dalam
                </p>
            </div>
        </div>
    </div>


</body>
</html>
