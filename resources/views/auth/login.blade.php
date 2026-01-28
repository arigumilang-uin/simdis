<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IDEAL</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Alpine.js x-cloak: Hide elements until Alpine loads --}}
    <style>
        [x-cloak] { display: none !important; }
    </style>

</head>
<body style="margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif;">

    <!-- Full page mesh gradient background -->
    <div class="mesh-gradient-bg" style="min-height: 100vh; display: flex; position: relative; overflow: hidden;">
        
        <!-- Left Side: Branding Area -->
        <div class="hidden lg:flex" style="width: 55%; flex-direction: column; padding: 40px 60px;">
            
            <!-- Logo + Nama Sekolah di pojok kiri atas (diperbesar) -->
            <div style="display: flex; align-items: center; gap: 16px;">
                <picture>
                    <source srcset="{{ asset('assets/images/logo_smk.webp') }}" type="image/webp">
                    <img src="{{ asset('assets/images/logo_smk.png') }}" alt="Logo" style="width: 56px; height: 56px; object-fit: contain;">
                </picture>
                <div>
                    <div style="color: white; font-size: 18px; font-weight: 600; letter-spacing: -0.3px;">SMK Negeri 1 Lubuk Dalam</div>
                    <div style="color: rgba(255,255,255,0.6); font-size: 12px; font-weight: 400;">Kabupaten Siak, Riau</div>
                </div>
            </div>
            
            <!-- Branding text dengan background bulat hijau gelap -->
            <div style="flex: 1; display: flex; align-items: center; justify-content: center; position: relative; padding-left: 40px;">
                <!-- Lingkaran hijau GELAP agar teks lebih mudah dibaca -->
                <div class="float-circle" style="position: absolute; width: 550px; height: 550px; background: rgba(4, 120, 87, 0.5); border-radius: 50%; left: 55%; transform: translateX(-50%);"></div>
                <div class="float-circle-delay" style="position: absolute; width: 420px; height: 420px; background: rgba(6, 95, 70, 0.4); border-radius: 50%; left: 55%; transform: translateX(-50%);"></div>
                
                <!-- Teks branding - Typography Cantik & Premium -->
                <div class="animate-enter delay-100" style="position: relative; z-index: 10; text-align: left; margin-left: 280px;">
                    <!-- Elegant Serif Font -->
                    <p style="color: rgba(255,255,255,0.95); font-family: 'Playfair Display', serif; font-size: 36px; margin: 0 0 8px 0; font-weight: 400; font-style: italic; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(0,0,0,0.1);">Selamat Datang di</p>
                    
                    <!-- Bold Modern Sans Font dengan Gradient Text -->
                    <h1 style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 150px; font-weight: 800; margin: 0 0 24px 0; letter-spacing: -6px; line-height: 0.85; 
                        background: linear-gradient(180deg, #ffffff 30%, #a7f3d0 100%); 
                        -webkit-background-clip: text; 
                        -webkit-text-fill-color: transparent; 
                        filter: drop-shadow(0 4px 25px rgba(0,0,0,0.2));">IDEAL</h1>
                    
                    <!-- Clean Sans Font - NORMALIZED WEIGHT -->
                    <p style="color: rgba(255,255,255,0.85); font-family: 'Plus Jakarta Sans', sans-serif; font-size: 20px; margin: 0; letter-spacing: 0.5px; line-height: 1.5; font-weight: 300; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        Integrated Discipline &<br>Educational Achievement Log
                    </p>
                </div>
            </div>
            
        
        </div>
        
        <!-- Right Side: Login Form -->
        <!-- Right Side: Login Form -->
        <div class="w-full flex flex-col items-center justify-start min-h-screen relative box-border p-5 pt-[115px] pb-0 lg:w-[42%] lg:justify-center lg:p-12 lg:min-h-screen">
            
            <!-- Mobile Header Top Right (Absolute) -->
            <div class="flex lg:hidden animate-enter" style="position: absolute; top: 24px; right: 24px; align-items: center; gap: 12px; z-index: 50; text-align: right;">
                <div>
                    <h1 style="color: white; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; font-weight: 700; margin: 0; line-height: 1.2; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">SMK Negeri 1 Lubuk Dalam</h1>
                    <p style="color: rgba(255,255,255,0.9); font-size: 11px; margin: 0; font-weight: 400; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">Kabupaten Siak, Riau</p>
                </div>
                <picture>
                    <source srcset="{{ asset('assets/images/logo_smk.webp') }}" type="image/webp">
                    <img src="{{ asset('assets/images/logo_smk.png') }}" alt="Logo" style="width: 36px; height: 36px; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
                </picture>
            </div>
            
            <div class="animate-enter delay-300" style="width: 100%; max-width: 340px;">
                
                <!-- Mobile Branding Center (IDEAL + Kepanjangan) -->
                <!-- Mobile Branding Center (IDEAL + Kepanjangan) -->
                <div class="flex lg:hidden animate-enter" style="align-items: center; justify-content: center; gap: 14px; margin-bottom: 20px; margin-top: 16px;">
                    <h1 style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 46px; font-weight: 800; margin: 0; letter-spacing: -2px; line-height: 1; color: white; text-shadow: 0 4px 10px rgba(0,0,0,0.15);">IDEAL</h1>
                    <div style="width: 1px; height: 32px; background: rgba(255,255,255,0.4);"></div>
                    <p style="color: rgba(255,255,255,0.95); font-size: 11px; margin: 0; line-height: 1.35; font-weight: 400; text-align: left; max-width: 130px; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                        Integrated Discipline &<br>Educational Achievement Log
                    </p>
                </div>
                
                <!-- White Login Card - Compact Desktop Version -->
                <div style="background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(20px); border-radius: 20px; box-shadow: 0 40px 80px -12px rgba(0, 0, 0, 0.4), 0 12px 24px -4px rgba(0, 0, 0, 0.15), inset 0 2px 2px rgba(255, 255, 255, 0.9), inset 0 -2px 4px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: center; border: 1px solid rgba(255,255,255,0.8); border-bottom: 2px solid rgba(200, 200, 200, 0.2);" class="min-h-auto p-5 lg:py-7 lg:px-7">
                    
                    <div class="text-center mb-5">
                        <h2 class="text-xl md:text-2xl" style="color: #111827; font-weight: 800; margin: 0; letter-spacing: -0.5px; font-family: 'Plus Jakarta Sans', sans-serif;">Silahkan Login</h2>
                    </div>

                    @if($errors->any())
                        <div x-data="{ show: true }" 
                             x-show="show" 
                             x-transition:leave="transition ease-in duration-200" 
                             x-transition:leave-start="opacity-100 transform scale-100" 
                             x-transition:leave-end="opacity-0 transform scale-95" 
                             class="mb-4" 
                             style="background: #fff1f2; border: 1px solid #ffe4e6; border-left: 4px solid #f43f5e; border-radius: 10px; padding: 12px; box-shadow: 0 4px 15px -3px rgba(244, 63, 94, 0.1); position: relative;">
                            
                            <div style="display: flex; align-items: start; gap: 14px;">
                                <!-- Icon -->
                                <div style="flex-shrink: 0; background: #ffe4e6; padding: 6px; border-radius: 8px;">
                                    <x-ui.icon name="alert-triangle" size="18" style="color: #f43f5e;" stroke-width="2.5" />
                                </div>
                                
                                <!-- Content -->
                                <div style="flex: 1; min-width: 0; padding-top: 4px;">
                                    <ul style="margin: 0; padding-left: 0; list-style: none; font-size: 14px; color: #9f1239; line-height: 1.5; font-weight: 600;">
                                        @foreach ($errors->all() as $error)
                                            <li style="margin-bottom: 4px;">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                
                                <!-- Close Button -->
                                <button @click="show = false" type="button" 
                                        style="flex-shrink: 0; margin-left: 4px; margin-top: -4px; background: none; border: none; padding: 6px; color: #f43f5e; opacity: 0.5; cursor: pointer; border-radius: 8px; transition: all 0.2s;" 
                                        onmouseover="this.style.opacity='1'; this.style.backgroundColor='#ffe4e6'" 
                                        onmouseout="this.style.opacity='0.5'; this.style.backgroundColor='transparent'">
                                    <x-ui.icon name="x" size="16" stroke-width="2.5" />
                                </button>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('login.post') }}" method="POST" 
                          x-data="{ 
                              showPassword: false, 
                              isLoading: false,
                              loadingText: 'Memeriksa...'
                          }"
                          @submit="isLoading = true; setTimeout(() => { loadingText = 'Mengarahkan...' }, 800)">
                        @csrf

                        <div class="mb-4">
                            <label for="username" class="text-[12px]" style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px;">Username</label>
                            <div class="input-group">
                                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus 
                                    class="input-field text-[13px]"
                                    style="padding: 12px 12px 12px 44px; border: 1px solid #cbd5e1; background-color: #ffffff; border-radius: 10px;"
                                    placeholder="NIP / NUPTK / Username"
                                    :readonly="isLoading"
                                    :class="{ 'bg-slate-100 cursor-not-allowed': isLoading }">
                                <div class="input-icon" style="left: 14px;">
                                    <x-ui.icon name="user" size="18" stroke-width="2" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="text-[12px]" style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px;">Password</label>
                            <div class="input-group">
                                <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required 
                                    class="input-field text-[13px]"
                                    style="padding: 12px 44px 12px 44px; border: 1px solid #cbd5e1; background-color: #ffffff; border-radius: 10px;"
                                    placeholder="Password anda"
                                    :readonly="isLoading"
                                    :class="{ 'bg-slate-100 cursor-not-allowed': isLoading }">
                                <div class="input-icon" style="left: 14px;">
                                    <x-ui.icon name="lock" size="18" stroke-width="2" />
                                </div>
                                <button type="button" @click="showPassword = !showPassword" class="input-icon-btn" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px; transition: all 0.2s;" onmouseover="this.style.color='#64748b'" onmouseout="this.style.color='#94a3b8'" :disabled="isLoading">
                                    <x-ui.icon name="eye" size="18" x-show="!showPassword" stroke-width="2" />
                                    <x-ui.icon name="eye-off" size="18" x-show="showPassword" stroke-width="2" />
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center mb-4" x-data="{ checked: false }">
                            <label class="flex items-center cursor-pointer select-none group">
                                <div class="relative flex items-center justify-center w-[18px] h-[18px]">
                                    <!-- Hidden Native Input -->
                                    <input id="remember" name="remember" type="checkbox" x-model="checked"
                                        class="absolute inset-0 w-full h-full opacity-0 z-10 cursor-pointer">
                                    
                                    <!-- Custom Visual Checkbox -->
                                    <div class="w-full h-full rounded-md border-2 transition-all duration-200 flex items-center justify-center shadow-sm"
                                         :class="checked ? 'bg-emerald-500 border-emerald-500' : 'bg-white border-slate-300 group-hover:border-slate-400'">
                                        <svg x-show="checked" 
                                             x-transition:enter="transition ease-out duration-200" 
                                             x-transition:enter-start="opacity-0 scale-50" 
                                             x-transition:enter-end="opacity-100 scale-100"
                                             class="w-2.5 h-2.5 text-white" 
                                             viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M10.6666 1.5L4.24992 7.91667L1.33325 5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                                <span class="ml-2 text-[12px] text-slate-500 font-medium group-hover:text-slate-600 transition-colors">Ingat saya</span>
                            </label>
                        </div>

                        <button type="submit" 
                            class="text-[13px] p-3"
                            style="width: 100%; background: linear-gradient(135deg, #059669, #10b981); color: white; font-weight: 700; border: none; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 8px 20px -5px rgba(16, 185, 129, 0.5); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); letter-spacing: 0.5px; position: relative; overflow: hidden;"
                            :class="{ 'opacity-80 cursor-wait': isLoading }"
                            :disabled="isLoading"
                            @mouseover="if(!isLoading) { $el.style.transform='translateY(-2px)'; $el.style.boxShadow='0 16px 30px -5px rgba(16, 185, 129, 0.6)'; }"
                            @mouseout="$el.style.transform='translateY(0)'; $el.style.boxShadow='0 8px 20px -5px rgba(16, 185, 129, 0.5)';">
                            
                            {{-- Default State --}}
                            <span x-show="!isLoading" class="flex items-center gap-2">
                                <span>MASUK SEKARANG</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </span>
                            
                            {{-- Loading State --}}
                            <span x-show="isLoading" x-cloak class="flex items-center gap-2">
                                <svg class="animate-spin" style="width: 16px; height: 16px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="loadingText"></span>
                            </span>
                        </button>

                    </form>

                    {{-- Separator --}}
                    <div class="flex items-center gap-3 my-4">
                        <div class="flex-1 h-px bg-slate-200"></div>
                        <span class="text-slate-400 text-[10px] font-medium uppercase tracking-wider">atau</span>
                        <div class="flex-1 h-px bg-slate-200"></div>
                    </div>

                    {{-- Google Login Button --}}
                    <a href="{{ route('auth.google') }}" 
                       class="text-[12px] p-2.5 group"
                       style="width: 100%; background: white; color: #374151; font-weight: 600; border: 1.5px solid #e5e7eb; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.25s ease; text-decoration: none;"
                       onmouseover="this.style.borderColor='#4285f4'; this.style.boxShadow='0 4px 12px rgba(66, 133, 244, 0.15)'; this.style.transform='translateY(-1px)';"
                       onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'; this.style.transform='translateY(0)';">
                        {{-- Google Logo SVG --}}
                        <svg width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        <span>Masuk dengan Google</span>
                    </a>

                    {{-- Info Text --}}
                    <p class="text-center text-[10px] text-slate-400 mt-3">
                        Login dengan Google hanya untuk akun yang sudah terdaftar.
                    </p>
                </div>
                
                
            </div>
        </div>
    </div>

</body>
</html>
