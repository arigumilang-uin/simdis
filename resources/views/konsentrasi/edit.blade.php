@extends('layouts.app')

@section('title', 'Edit Konsentrasi')

@section('page-header')
    <x-page-header 
        title="Edit Konsentrasi" 
        subtitle="Perbarui data konsentrasi keahlian."
    />
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Edit Konsentrasi</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('konsentrasi.update', $konsentrasi->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <x-forms.select 
                    name="jurusan_id" 
                    label="Jurusan (Program Keahlian)" 
                    required 
                >
                    <option value="">-- Pilih Jurusan --</option>
                    @foreach($jurusanList ?? [] as $j)
                        <option value="{{ $j->id }}" {{ old('jurusan_id', $konsentrasi->jurusan_id) == $j->id ? 'selected' : '' }}>
                            {{ $j->nama_jurusan }}
                        </option>
                    @endforeach
                </x-forms.select>
                
                <x-forms.input 
                    name="kode_konsentrasi" 
                    label="Kode Konsentrasi" 
                    :value="$konsentrasi->kode_konsentrasi" 
                    placeholder="Contoh: TPB" 
                    maxlength="20" 
                />
                
                <x-forms.input 
                    name="nama_konsentrasi" 
                    label="Nama Konsentrasi" 
                    :value="$konsentrasi->nama_konsentrasi" 
                    placeholder="Contoh: Teknik Pembangkit Biomassa" 
                    required 
                />
                
                <x-forms.textarea 
                    name="deskripsi" 
                    label="Deskripsi" 
                    :value="$konsentrasi->deskripsi" 
                    placeholder="Deskripsi singkat tentang konsentrasi ini (opsional)" 
                    rows="3" 
                />
                
                <div class="form-group">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" 
                               class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               {{ old('is_active', $konsentrasi->is_active) ? 'checked' : '' }}>
                        <span class="text-gray-700">Konsentrasi Aktif</span>
                    </label>
                </div>
                
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="save" size="18" />
                        <span>Simpan Perubahan</span>
                    </button>
                    <a href="{{ route('konsentrasi.index') }}" class="btn btn-secondary">
                        <x-ui.icon name="x" size="18" />
                        <span>Batal</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
