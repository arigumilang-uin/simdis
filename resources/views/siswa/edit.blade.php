@extends('layouts.app')

@section('title', 'Edit Siswa')

@section('page-header')
    <x-page-header 
        title="Edit Siswa" 
        subtitle="Perbarui data siswa."
    />
@endsection

@section('content')
<x-forms.card 
    title="Form Edit Siswa"
    :action="route('siswa.update', $siswa->id)"
    method="PUT"
>
    {{-- Data Siswa --}}
    <x-forms.section title="Data Siswa" icon="user" variant="default">
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
            :value="$siswa->kelas_id"
            placeholder="Pilih Kelas"
        />
    </x-forms.section>
    
    {{-- Data Wali Murid --}}
    <x-forms.section title="Data Wali Murid" icon="users" variant="info">
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
            :value="$siswa->wali_murid_user_id"
            placeholder="-- Tidak ada --"
        />
    </x-forms.section>
    
    <x-forms.actions 
        submitLabel="Simpan Perubahan"
        :cancelUrl="route('siswa.index')"
    />
</x-forms.card>
@endsection
