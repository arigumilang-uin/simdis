@extends('layouts.app')

@section('title', 'Edit Siswa')
@section('subtitle', 'Perbarui data siswa.')
@section('page-header', true)

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Edit Siswa</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('siswa.update', $siswa->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                {{-- Data Siswa --}}
                <x-forms.input 
                    name="nisn" 
                    label="NISN" 
                    :value="$siswa->nisn" 
                    maxlength="20"
                    required 
                />
                
                <x-forms.input 
                    name="nama_siswa" 
                    label="Nama Lengkap" 
                    :value="$siswa->nama_siswa" 
                    required 
                />
                
                <x-forms.select 
                    name="kelas_id" 
                    label="Kelas" 
                    required
                    :options="$kelas"
                    optionValue="id"
                    optionLabel="nama_kelas"
                    :selected="$siswa->kelas_id"
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
                        :value="$siswa->nomor_hp_wali_murid" 
                        placeholder="Contoh: 08123456789" 
                    />
                    
                    <x-forms.select 
                        name="wali_murid_id" 
                        label="Akun Wali Murid" 
                        :options="$waliMurid"
                        optionValue="id"
                        optionLabel="username"
                        :selected="$siswa->wali_murid_user_id"
                        placeholder="-- Tidak ada --"
                    />
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="save" size="18" />
                        <span>Simpan Perubahan</span>
                    </button>
                    <a href="{{ route('siswa.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
