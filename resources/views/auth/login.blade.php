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
                
                <!-- White Login Card - Polished with Glassmorphism feel -->
                <!-- White Login Card - Polished with Glassmorphism feel -->
                <!-- White Login Card - Polished with Strong Elevated Effect -->
                <div style="background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(20px); border-radius: 24px; box-shadow: 0 40px 80px -12px rgba(0, 0, 0, 0.4), 0 12px 24px -4px rgba(0, 0, 0, 0.15), inset 0 2px 2px rgba(255, 255, 255, 0.9), inset 0 -2px 4px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: center; border: 1px solid rgba(255,255,255,0.8); border-bottom: 2px solid rgba(200, 200, 200, 0.2);" class="min-h-auto lg:min-h-[480px] p-6 lg:py-11 lg:px-9">
                    
                    <div class="text-center mb-6 md:mb-10">
                        <h2 class="text-2xl md:text-[26px]" style="color: #111827; font-weight: 800; margin: 0 0 10px 0; letter-spacing: -0.5px; font-family: 'Plus Jakarta Sans', sans-serif;">Silahkan Login</h2>
                        <p class="text-[13px] md:text-[15px]" style="color: #64748b; margin: 0; font-weight: 400; line-height: 1.5;">Masukkan kredensial akun Anda<br>untuk mengakses sistem</p>
                    </div>

                    @if($errors->any())
                        <div x-data="{ show: true }" 
                             x-show="show" 
                             x-transition:leave="transition ease-in duration-200" 
                             x-transition:leave-start="opacity-100 transform scale-100" 
                             x-transition:leave-end="opacity-0 transform scale-95" 
                             class="mb-6 md:mb-8" 
                             style="background: #fff1f2; border: 1px solid #ffe4e6; border-left: 4px solid #f43f5e; border-radius: 12px; padding: 16px; box-shadow: 0 4px 15px -3px rgba(244, 63, 94, 0.1); position: relative;">
                            
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

                    <form action="{{ route('login.post') }}" method="POST" x-data="{ showPassword: false }">
                        @csrf

                        <div class="mb-5 md:mb-7">
                            <label for="username" class="text-[13px] md:text-[14px]" style="display: block; font-weight: 600; color: #334155; margin-bottom: 10px;">Username</label>
                            <div class="input-group">
                                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus 
                                    class="input-field text-[13px] md:text-[15px]"
                                    style="padding: 16px 16px 16px 52px; border: 1px solid #cbd5e1; background-color: #ffffff;"
                                    placeholder="NIP / NUPTK / No. HP">
                                <div class="input-icon">
                                    <x-ui.icon name="user" size="20" stroke-width="2" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-5 md:mb-7">
                            <label for="password" class="text-[13px] md:text-[14px]" style="display: block; font-weight: 600; color: #334155; margin-bottom: 10px;">Password</label>
                            <div class="input-group">
                                <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required 
                                    class="input-field text-[13px] md:text-[15px]"
                                    style="padding: 16px 52px 16px 52px; border: 1px solid #cbd5e1; background-color: #ffffff;"
                                    placeholder="Password anda">
                                <div class="input-icon">
                                    <x-ui.icon name="lock" size="20" stroke-width="2" />
                                </div>
                                <button type="button" @click="showPassword = !showPassword" class="input-icon-btn" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px; transition: all 0.2s;" onmouseover="this.style.color='#64748b'" onmouseout="this.style.color='#94a3b8'">
                                    <x-ui.icon name="eye" size="20" x-show="!showPassword" stroke-width="2" />
                                    <x-ui.icon name="eye-off" size="20" x-show="showPassword" stroke-width="2" />
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center mb-6 md:mb-10" x-data="{ checked: false }">
                            <label class="flex items-center cursor-pointer select-none group">
                                <div class="relative flex items-center justify-center w-[20px] h-[20px] md:w-[22px] md:h-[22px]">
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
                                             class="w-3 h-3 md:w-3.5 md:h-3.5 text-white" 
                                             viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M10.6666 1.5L4.24992 7.91667L1.33325 5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                                <span class="ml-2.5 text-[14px] md:text-[15px] text-slate-500 font-medium group-hover:text-slate-600 transition-colors">Ingat saya</span>
                            </label>
                        </div>

                        <button type="submit" 
                            class="text-[14px] md:text-[16px] p-4 md:p-[18px]"
                            style="width: 100%; background: linear-gradient(135deg, #059669, #10b981); color: white; font-weight: 700; border: none; border-radius: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.5); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); letter-spacing: 0.5px; position: relative; overflow: hidden;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 20px 35px -5px rgba(16, 185, 129, 0.6)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px -5px rgba(16, 185, 129, 0.5)';">
                            <span style="position: relative; z-index: 1;">MASUK SEKARANG</span>
                            <x-ui.icon name="arrow-right" size="20" stroke-width="2.5" style="position: relative; z-index: 1;" />
                        </button>

                    </form>
                </div>
                
                
            </div>
        </div>
    </div>

</body>
</html>
