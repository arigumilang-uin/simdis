<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lengkapi Profil | {{ config('app.name', 'SMK Negeri 1') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#eef2ff', 100: '#e0e7ff', 600: '#4f46e5', 700: '#4338ca' },
                        slate: { 50: '#f8fafc', 800: '#1e293b' }
                    }
                }
            }
        }
    </script>
    <style>
        .bg-pattern {
            background-image: radial-gradient(#e0e7ff 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="h-screen overflow-hidden text-slate-800 bg-white">

    <div class="flex h-full w-full">
        
        <!-- LEFT PANEL: Visual Identity (Matches 'Login' layout structure) -->
        <div class="hidden lg:flex w-5/12 bg-indigo-900 relative items-center justify-center overflow-hidden">
            <!-- Background Gradients -->
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 to-indigo-900 opacity-90"></div>
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
            <div class="absolute bottom-0 right-0 w-80 h-80 bg-blue-500 rounded-full mix-blend-overlay filter blur-3xl opacity-20"></div>

            <!-- Content -->
            <div class="relative z-10 p-12 w-full max-w-lg">
                <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-8 border border-white/20 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.2-2.85.582-4.161" />
                    </svg>
                </div>

                <h1 class="text-4xl font-extrabold text-white tracking-tight leading-tight mb-4">Verifikasi<br><span class="text-indigo-200">Keamanan Akun</span></h1>
                <p class="text-indigo-100 text-lg leading-relaxed font-medium opacity-90 mb-8">
                    Sistem mendeteksi profil Anda belum lengkap. Mohon perbarui data untuk melanjutkan akses ke dashboard.
                </p>

                <!-- User Card Mini -->
                <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 shadow-lg">
                    <div class="w-12 h-12 rounded-xl bg-white text-indigo-700 flex items-center justify-center font-black text-xl shadow-sm shrink-0">
                        {{ strtoupper(substr($user->nama, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-white text-base tracking-wide">{{ $user->nama }}</div>
                        <div class="text-[11px] bg-indigo-500/50 px-2 py-0.5 rounded text-white uppercase tracking-wider font-bold inline-block mt-1">{{ $user->role->nama_role }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: Form (Scrollable) -->
        <div class="w-full lg:w-7/12 h-full overflow-y-auto bg-white bg-pattern relative">
            <div class="min-h-full flex flex-col justify-center items-center py-10">
                
                <div class="w-full max-w-2xl px-8">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                        <span class="w-2 h-8 bg-indigo-600 rounded-full"></span>
                        Formulir Data Diri
                    </h2>
                    <p class="text-slate-500 mt-2 ml-5">Perbarui informasi akun Anda di bawah ini.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-8 p-4 rounded-xl bg-rose-50 border border-rose-100 text-rose-700 text-sm font-medium flex items-start gap-3 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-0.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div class="space-y-1">
                            <p class="font-bold">Perhatian:</p>
                            <ul class="list-disc list-inside opacity-90">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('profile.complete.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Username -->
                        <div class="group">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 group-focus-within:text-indigo-600 transition-colors">Username <span class="text-slate-300 font-normal ml-0.5 normal-case">(Opsional)</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                    <i class="fas fa-user text-sm"></i>
                                </span>
                                <input type="text" name="username" value="{{ old('username', $user->username) }}" 
                                    class="w-full pl-9 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-semibold focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none shadow-sm"
                                    placeholder="Username">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="group">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 group-focus-within:text-indigo-600 transition-colors">Email <span class="text-rose-400">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                    <i class="fas fa-envelope text-sm"></i>
                                </span>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                    class="w-full pl-9 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-semibold focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none shadow-sm"
                                    placeholder="email@sekolah.sch.id">
                            </div>
                        </div>
                    </div>

                    @if(!$isWaliMurid)
                    <div class="mb-6 group">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 group-focus-within:text-indigo-600 transition-colors">WhatsApp <span class="text-slate-300 font-normal ml-0.5 normal-case">(Opsional)</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <i class="fab fa-whatsapp text-lg"></i>
                            </span>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                                class="w-full pl-9 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-semibold focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none shadow-sm"
                                placeholder="0812xxxxxxxx">
                        </div>
                    </div>
                    @endif

                    @if($needsPasswordChange)
                    <div class="mt-8 pt-8 border-t border-slate-100 relative">
                        <div class="absolute top-0 left-0 bg-white pr-4 text-xs font-bold text-slate-400 uppercase tracking-widest -translate-y-1/2">Keamanan Akun</div>
                        
                        <div class="space-y-5">
                            <div>
                                 <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Password Lama</label>
                                 <div class="relative">
                                     <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fas fa-lock text-sm"></i></span>
                                     <input type="password" name="current_password" id="current_password" required
                                        class="w-full pl-9 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-sm font-medium focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none placeholder:text-slate-400"
                                        placeholder="Password Lama (dari operator)">
                                     <button type="button" onclick="togglePassword('current_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-600 transition-colors outline-none">
                                         <i class="fas fa-eye"></i>
                                     </button>
                                 </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Password Baru</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fas fa-key text-sm"></i></span>
                                        <input type="password" name="password" id="password" required
                                            class="w-full pl-9 pr-10 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-medium focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none placeholder:text-slate-400"
                                            placeholder="Minimal 6 karakter">
                                        <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-600 transition-colors outline-none">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Ulangi Password</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fas fa-check-double text-sm"></i></span>
                                        <input type="password" name="password_confirmation" id="password_confirmation" required
                                            class="w-full pl-9 pr-10 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-medium focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none placeholder:text-slate-400"
                                            placeholder="Ketik ulang password">
                                        <button type="button" onclick="togglePassword('password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-600 transition-colors outline-none">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="mt-10 flex items-center justify-between gap-6">
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-slate-400 hover:text-slate-600 text-sm font-bold px-1 transition-colors hover:underline">
                                <i class="fas fa-arrow-left mr-1"></i> Logout
                            </button>
                        </form>
                        
                        <button type="submit" class="flex-1 md:flex-none bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-8 rounded-xl shadow-lg shadow-indigo-500/30 transition-all transform hover:-translate-y-0.5 active:translate-y-0 text-sm">
                            Simpan & Lanjutkan <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>

                <div class="mt-12 pt-6 border-t border-slate-100 text-center text-xs text-slate-400 font-medium">
                    &copy; {{ date('Y') }} {{ school_name() }}. All rights reserved.
                </div>
            </div>
        </div>
    </div>
    
    @if(app()->environment('local'))
    <div class="fixed bottom-4 right-4 z-50">
        <a href="{{ route('profile.complete.skip') }}" class="group flex items-center gap-2 bg-white/80 backdrop-blur-md px-4 py-2 rounded-full border border-slate-200 shadow-sm hover:shadow-md transition-all text-slate-500 hover:text-indigo-600 no-underline">
            <span class="w-2 h-2 rounded-full bg-emerald-500 group-hover:bg-indigo-500 transition-colors"></span>
            <span class="text-[11px] font-bold uppercase tracking-wider">Dev Skip</span>
        </a>
    </div>
    @endif
    
    <!-- Icons Script -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        function togglePassword(fieldId) {
            const input = document.getElementById(fieldId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

</body>
</html>
