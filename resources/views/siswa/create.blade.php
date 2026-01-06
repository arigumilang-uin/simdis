@extends('layouts.app')

@section('title', 'Tambah Siswa')

@section('page-header')
    <x-page-header 
        title="Tambah Siswa" 
        subtitle="Tambahkan data siswa baru ke sistem."
    />
@endsection

@section('content')
<x-forms.card 
    title="Form Tambah Siswa"
    :action="route('siswa.store')"
    method="POST"
>
    {{-- Data Siswa --}}
    <x-forms.section title="Data Siswa" icon="user" variant="default">
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
    </x-forms.section>
    
    {{-- Data Wali Murid --}}
    <x-forms.section title="Data Wali Murid" icon="users" variant="info">
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
        
        <x-forms.checkbox 
            name="create_wali"
            label="Buat akun wali murid secara otomatis"
            description="Sistem akan membuat akun wali murid dengan username berdasarkan NISN."
            variant="info"
        />
    </x-forms.section>
    
    <x-forms.actions 
        submitLabel="Simpan Data Siswa"
        :cancelUrl="route('siswa.index')"
    />
</x-forms.card>
@endsection
