@extends('layouts.app')

@section('content')

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: { colors: { primary: '#3b82f6', slate: { 800: '#1e293b', 900: '#0f172a' } } }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap">

    <div class="w-full max-w-2xl">
        
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 m-0">Edit Data Kelas</h1>
                <p class="text-slate-500 text-sm mt-1">Ubah informasi rombongan belajar</p>
            </div>
            <a href="{{ route('kelas.index') }}" class="text-sm font-medium text-slate-500 hover:text-blue-600 no-underline flex items-center gap-1 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
            
            <div class="bg-slate-50/80 px-8 py-4 border-b border-slate-100 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">ID Kelas: {{ $kelas->id }}</span>
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            </div>

            <form action="{{ route('kelas.update', $kelas) }}" method="POST" class="p-8">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Nama Kelas <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_kelas" class="form-input" value="{{ old('nama_kelas', $kelas->nama_kelas) }}" placeholder="Contoh: X RPL 1" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Tingkat <span class="text-rose-500">*</span></label>
                        <select name="tingkat" class="form-input cursor-pointer" required>
                            <option value="">Pilih Tingkat</option>
                            <option value="X" {{ old('tingkat', $kelas->tingkat) == 'X' ? 'selected' : '' }}>Kelas X</option>
                            <option value="XI" {{ old('tingkat', $kelas->tingkat) == 'XI' ? 'selected' : '' }}>Kelas XI</option>
                            <option value="XII" {{ old('tingkat', $kelas->tingkat) == 'XII' ? 'selected' : '' }}>Kelas XII</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jurusan <span class="text-rose-500">*</span></label>
                        <select name="jurusan_id" class="form-input cursor-pointer" required>
                            <option value="">Pilih Jurusan</option>
                            @foreach($jurusanList as $j)
                                <option value="{{ $j->id }}" {{ $kelas->jurusan_id == $j->id ? 'selected' : '' }}>
                                    {{ $j->nama_jurusan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label flex justify-between">
                        <span>Wali Kelas</span>
                        <span class="text-xs font-normal text-slate-400 italic">Opsional</span>
                    </label>
                    <select name="wali_kelas_user_id" class="form-input cursor-pointer">
                        <option value="">-- Belum Ada Wali Kelas --</option>
                        @foreach($waliList as $w)
                            <option value="{{ $w->id }}" {{ $kelas->wali_kelas_user_id == $w->id ? 'selected' : '' }}>
                                {{ $w->nama }} ({{ $w->username }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-6 mt-2 flex flex-col-reverse md:flex-row gap-4 border-t border-slate-100">
                    <a href="{{ route('kelas.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<style>
    .page-wrap { background: #f8fafc; min-height: 100vh; padding: 2rem 1.5rem; font-family: 'Inter', sans-serif; display: flex; justify-content: center; }
    
    /* Input Styling Lebih Rapi */
    .form-group { margin-bottom: 1.25rem; }
    .form-label { display: block; font-size: 0.875rem; font-weight: 600; color: #475569; margin-bottom: 0.5rem; }
    .form-input { 
        width: 100%; 
        padding: 0.75rem 1rem; 
        border-radius: 0.75rem; 
        border: 1px solid #cbd5e1; 
        background-color: #ffffff; 
        color: #1e293b; 
        transition: all 0.2s; 
        font-size: 0.95rem; 
        line-height: 1.5;
    }
    .form-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
    
    /* Custom Select Arrow */
    select.form-input {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }

    /* Buttons */
    .btn-primary { background: #2563eb; color: white; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 600; border: none; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; width: 100%; }
    .btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3); }
    
    .btn-secondary { background: white; border: 1px solid #e2e8f0; color: #64748b; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 600; text-decoration: none; display: inline-block; text-align: center; width: 100%; transition: 0.2s; }
    .btn-secondary:hover { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
</style>
@endsection