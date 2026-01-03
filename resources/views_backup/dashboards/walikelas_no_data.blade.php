<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas Belum Diatur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    animation: { 'bounce-slow': 'bounce 3s infinite' }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 h-screen w-full flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-100 p-8 text-center relative overflow-hidden">
        
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-amber-400 to-orange-500"></div>

        <div class="mx-auto w-20 h-20 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mb-6 animate-bounce-slow">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                <line x1="8" x2="8" y1="21" y2="21"/><line x1="16" x2="16" y1="21" y2="21"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-slate-800 mb-2">Kelas Belum Diatur</h1>
        
        <div class="text-slate-500 text-sm mb-6 leading-relaxed">
            <p class="mb-2">Halo, <span class="font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded">{{ Auth::user()->username }}</span> 👋</p>
            <p>
                Sistem mendeteksi Anda sebagai <strong>Wali Kelas</strong>, namun belum ada data Kelas yang dihubungkan dengan akun Anda.
            </p>
        </div>

        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-8 text-left flex items-start gap-3">
            <div class="mt-0.5 p-1 bg-blue-100 rounded text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/></svg>
            </div>
            <div class="text-xs text-blue-800">
                <strong>Apa yang harus dilakukan?</strong><br>
                Mohon hubungi <span class="font-bold">Operator Sekolah</span> atau <span class="font-bold">Waka Kurikulum</span> agar akun Anda segera di-mapping ke kelas yang sesuai.
            </div>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full py-3 px-4 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition-all shadow-lg shadow-slate-200 hover:-translate-y-0.5 flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                Keluar Aplikasi
            </button>
        </form>

        <p class="mt-6 text-xs text-slate-400">
            &copy; {{ date('Y') }} Sistem Informasi Sekolah
        </p>

    </div>

</body>
</html>