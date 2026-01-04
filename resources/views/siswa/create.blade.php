@extends('layouts.app')

@section('title', 'Tambah Siswa')

@section('page-header')
    <x-page-header 
        title="Tambah Siswa" 
        subtitle="Tambahkan data siswa baru ke sistem."
    />
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Siswa</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('siswa.store') }}" method="POST" class="space-y-6">
                @csrf
                
                {{-- Data Siswa --}}
                <x-forms.input 
                    name="nisn" 
                    label="NISN" 
                    placeholder="10 digit NISN" 
                    required 
                    help="NISN harus unik untuk setiap siswa."
                    maxlength="20"
                />
                
                <x-forms.input 
                    name="nama_siswa" 
                    label="Nama Lengkap" 
                    placeholder="Nama lengkap siswa" 
                    required 
                />
                
                <x-forms.select 
                    name="kelas_id" 
                    label="Kelas" 
                    required
                    :options="$kelas"
                    optionValue="id"
                    optionLabel="nama_kelas"
                    placeholder="Pilih Kelas"
                />
                
                {{-- Data Wali Murid --}}
                <div class="border-t border-gray-100 pt-6 space-y-4">
                    <h4 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                        <x-ui.icon name="users" size="18" class="text-gray-400" />
                        Data Wali Murid
                    </h4>
                    
                    <x-forms.input 
                        name="nomor_hp_wali_murid" 
                        label="No. HP Wali Murid" 
                        placeholder="Contoh: 08123456789" 
                        help="Digunakan untuk menghubungi wali murid via WhatsApp."
                    />
                    
                    <x-forms.select 
                        name="wali_murid_id" 
                        label="Hubungkan ke Akun Wali Murid" 
                        :options="$waliMurid"
                        optionValue="id"
                        optionLabel="username"
                        placeholder="-- Tidak ada / Buat baru --"
                        help="Pilih akun wali murid yang sudah ada, atau centang opsi di bawah untuk membuat otomatis."
                    />
                    
                    <div class="form-group">
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-blue-50 rounded-lg border border-blue-100 hover:bg-blue-100 transition-colors">
                            <input 
                                type="checkbox" 
                                name="create_wali" 
                                value="1" 
                                {{ old('create_wali') ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <div>
                                <span class="text-sm font-medium text-blue-800">Buat akun wali murid secara otomatis</span>
                                <p class="text-xs text-blue-600 mt-0.5">Sistem akan membuat akun wali murid dengan username berdasarkan NISN.</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="save" size="18" />
                        <span>Simpan Data Siswa</span>
                    </button>
                    <a href="{{ route('siswa.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
