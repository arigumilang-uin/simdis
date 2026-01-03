@extends('layouts.app')

@section('title', isset($konsentrasi) ? 'Edit Konsentrasi' : 'Tambah Konsentrasi')
@section('subtitle', isset($konsentrasi) ? 'Perbarui data konsentrasi keahlian.' : 'Tambahkan konsentrasi keahlian baru.')
@section('page-header', true)

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form {{ isset($konsentrasi) ? 'Edit' : 'Tambah' }} Konsentrasi</h3>
        </div>
        <div class="card-body">
            <form action="{{ isset($konsentrasi) ? route('konsentrasi.update', $konsentrasi->id) : route('konsentrasi.store') }}" method="POST" class="space-y-6">
                @csrf
                @if(isset($konsentrasi))
                    @method('PUT')
                @endif
                
                <x-forms.select 
                    name="jurusan_id" 
                    label="Jurusan (Program Keahlian)" 
                    required 
                    :options="$jurusanList"
                    optionValue="id"
                    optionLabel="nama_jurusan"
                    :selected="old('jurusan_id', $konsentrasi->jurusan_id ?? '')"
                    placeholder="-- Pilih Jurusan --"
                    help="Konsentrasi ini akan menjadi bagian dari jurusan yang dipilih."
                />
                
                <x-forms.input 
                    name="kode_konsentrasi" 
                    label="Kode Konsentrasi" 
                    :value="$konsentrasi->kode_konsentrasi ?? ''"
                    placeholder="Contoh: TPB" 
                    maxlength="20"
                />
                
                <x-forms.input 
                    name="nama_konsentrasi" 
                    label="Nama Konsentrasi" 
                    required
                    :value="$konsentrasi->nama_konsentrasi ?? ''"
                    placeholder="Contoh: Teknik Pembangkit Biomassa" 
                />
                
                <x-forms.textarea 
                    name="deskripsi" 
                    label="Deskripsi" 
                    rows="3"
                    :value="$konsentrasi->deskripsi ?? ''"
                    placeholder="Deskripsi singkat tentang konsentrasi ini (opsional)" 
                />
                
                <div class="form-group">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" 
                               class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               {{ old('is_active', $konsentrasi->is_active ?? true) ? 'checked' : '' }}>
                        <span class="text-gray-700">Konsentrasi Aktif</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">Konsentrasi yang tidak aktif tidak akan muncul di form input lain.</p>
                </div>
                
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="save" size="18" />
                        <span>Simpan</span>
                    </button>
                    <a href="{{ route('konsentrasi.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
