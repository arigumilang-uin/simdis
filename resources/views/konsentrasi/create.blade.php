@extends('layouts.app')

@section('title', isset($konsentrasi) ? 'Edit Konsentrasi' : 'Tambah Konsentrasi')

@section('page-header')
    <x-page-header 
        :title="isset($konsentrasi) ? 'Edit Konsentrasi' : 'Tambah Konsentrasi'" 
        :subtitle="isset($konsentrasi) ? 'Perbarui data konsentrasi keahlian.' : 'Tambahkan konsentrasi keahlian baru.'"
    />
@endsection

@section('content')
<x-forms.card 
    :title="'Form ' . (isset($konsentrasi) ? 'Edit' : 'Tambah') . ' Konsentrasi'"
    :action="isset($konsentrasi) ? route('konsentrasi.update', $konsentrasi->id) : route('konsentrasi.store')"
    :method="isset($konsentrasi) ? 'PUT' : 'POST'"
>
    <x-forms.select 
        name="jurusan_id" 
        label="Jurusan (Program Keahlian)" 
        required 
        :options="$jurusanList"
        optionValue="id"
        optionLabel="nama_jurusan"
        :value="$konsentrasi->jurusan_id ?? ''"
        placeholder="-- Pilih Jurusan --"
        help="Konsentrasi ini akan menjadi bagian dari jurusan yang dipilih."
    />
    
    <x-forms.grid :cols="2">
        <x-forms.input 
            name="kode_konsentrasi" 
            label="Kode Konsentrasi" 
            :value="$konsentrasi->kode_konsentrasi ?? ''"
            placeholder="Contoh: TPB" 
            maxlength="20"
            help="Kode singkat untuk konsentrasi (opsional)"
        />
        
        <x-forms.input 
            name="nama_konsentrasi" 
            label="Nama Konsentrasi" 
            required
            :value="$konsentrasi->nama_konsentrasi ?? ''"
            placeholder="Contoh: Teknik Pembangkit Biomassa" 
        />
    </x-forms.grid>
    
    <x-forms.textarea 
        name="deskripsi" 
        label="Deskripsi" 
        rows="3"
        :value="$konsentrasi->deskripsi ?? ''"
        placeholder="Deskripsi singkat tentang konsentrasi ini (opsional)" 
    />
    
    <x-forms.checkbox 
        name="is_active" 
        label="Konsentrasi Aktif"
        description="Konsentrasi yang tidak aktif tidak akan muncul di form input lain."
        :checked="$konsentrasi->is_active ?? true"
        variant="info"
    />
    
    <x-forms.actions 
        submitLabel="Simpan Konsentrasi"
        :cancelUrl="route('konsentrasi.index')"
    />
</x-forms.card>
@endsection
