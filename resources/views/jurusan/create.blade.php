@extends('layouts.app')

@section('title', isset($jurusan) ? 'Edit Jurusan' : 'Tambah Jurusan')

@section('page-header')
    <x-page-header 
        :title="isset($jurusan) ? 'Edit Jurusan' : 'Tambah Jurusan'" 
        :subtitle="isset($jurusan) ? 'Perbarui data jurusan.' : 'Tambahkan jurusan baru.'"
    />
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form {{ isset($jurusan) ? 'Edit' : 'Tambah' }} Jurusan</h3>
        </div>
        <div class="card-body">
            <form action="{{ isset($jurusan) ? route('jurusan.update', $jurusan->id) : route('jurusan.store') }}" method="POST" class="space-y-6">
                @csrf
                @if(isset($jurusan))
                    @method('PUT')
                @endif
                
                <x-forms.input 
                    name="kode_jurusan" 
                    label="Kode Jurusan" 
                    :value="$jurusan->kode_jurusan ?? ''" 
                    placeholder="Contoh: TKJ" 
                    required 
                />
                
                <x-forms.input 
                    name="nama_jurusan" 
                    label="Nama Jurusan" 
                    :value="$jurusan->nama_jurusan ?? ''" 
                    placeholder="Contoh: Teknik Komputer dan Jaringan" 
                    required 
                />
                
                <x-forms.select 
                    name="kaprodi_user_id" 
                    label="Kaprodi" 
                    :options="$kaprodiList"
                    optionValue="id"
                    optionLabel="username"
                    :selected="$jurusan->kaprodi_user_id ?? ''"
                    placeholder="-- Pilih Kaprodi --"
                />
                
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="save" size="18" />
                        <span>Simpan</span>
                    </button>
                    <a href="{{ route('jurusan.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
