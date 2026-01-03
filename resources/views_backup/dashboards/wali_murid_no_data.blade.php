<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Tidak Ditemukan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    animation: { 'fade-in': 'fadeIn 0.5s ease-out' },
                    keyframes: { fadeIn: { '0%': { opacity: '0', transform: 'translateY(10px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } } }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100 animate-fade-in text-center p-8">
        
        <div class="mb-6 inline-flex items-center justify-center w-20 h-20 rounded-full bg-rose-50 text-rose-500 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <line x1="17" x2="22" y1="8" y2="13"/><line x1="22" x2="17" y1="8" y2="13"/>
            </svg>
        </div>

        <h2 class="text-2xl font-bold text-slate-800 mb-2">Akun Belum Terhubung</h2>
        
        <p class="text-slate-500 text-sm leading-relaxed mb-6">
            Halo Wali Murid, akun Anda saat ini aktif namun 
            <span class="font-bold text-slate-700">belum ditautkan dengan data Siswa manapun</span> 
            di dalam sistem.
        </p>

        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-8 text-left flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/></svg>
            <div class="text-sm text-blue-800">
                <strong>Solusi:</strong><br>
                Silakan hubungi <span class="underline decoration-blue-400">Operator Sekolah</span> atau Wali Kelas untuk melakukan <em>mapping</em> (penautan) data anak Anda ke akun ini.
            </div>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full inline-flex justify-center items-center gap-2 px-5 py-3 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl transition-all shadow-lg shadow-slate-200 hover:-translate-y-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                Keluar Aplikasi
            </button>
        </form>

    </div>

    <div class="absolute bottom-6 text-slate-400 text-xs text-center w-full">
        &copy; {{ date('Y') }} Sistem Informasi Manajemen Sekolah
    </div>

</body>
</html>