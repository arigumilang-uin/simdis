@extends('layouts.app')

@section('title', 'Edit Jurusan')

@section('page-header')
    <x-page-header 
        title="Edit Jurusan" 
        subtitle="Perbarui data jurusan."
    />
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Edit Jurusan</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('jurusan.update', $jurusan->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="kode_jurusan" class="form-label form-label-required">Kode Jurusan</label>
                    <input type="text" id="kode_jurusan" name="kode_jurusan" 
                           value="{{ old('kode_jurusan', $jurusan->kode_jurusan) }}"
                           class="form-input @error('kode_jurusan') error @enderror" required>
                    @error('kode_jurusan')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="nama_jurusan" class="form-label form-label-required">Nama Jurusan</label>
                    <input type="text" id="nama_jurusan" name="nama_jurusan" 
                           value="{{ old('nama_jurusan', $jurusan->nama_jurusan) }}"
                           class="form-input @error('nama_jurusan') error @enderror" required>
                    @error('nama_jurusan')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="kaprodi_user_id" class="form-label">Kaprodi</label>
                    <select id="kaprodi_user_id" name="kaprodi_user_id" class="form-input form-select @error('kaprodi_user_id') error @enderror">
                        <option value="">-- Pilih Kaprodi --</option>
                        @foreach($kaprodiList ?? [] as $k)
                            <option value="{{ $k->id }}" {{ old('kaprodi_user_id', $jurusan->kaprodi_user_id) == $k->id ? 'selected' : '' }}>
                                {{ $k->username }}
                            </option>
                        @endforeach
                    </select>
                    @error('kaprodi_user_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('jurusan.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
