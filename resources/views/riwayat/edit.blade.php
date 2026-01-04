@extends('layouts.app')

@section('title', 'Edit Pelanggaran')

@section('page-header')
    <x-page-header 
        title="Edit Pelanggaran" 
        subtitle="Perbarui data pelanggaran."
    />
@endsection

@section('content')
<div class="max-w-3xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Edit Pelanggaran</h3>
        </div>
        <div class="card-body">
            {{-- Info Siswa --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-xl">
                <p class="text-sm text-gray-500">Siswa</p>
                <p class="font-semibold text-gray-800">{{ $riwayat->siswa->nama_siswa ?? '-' }}</p>
                <p class="text-sm text-gray-500">{{ $riwayat->siswa->nisn ?? '' }} • {{ $riwayat->siswa->kelas->nama_kelas ?? '-' }}</p>
            </div>
            
            <form action="{{ route('riwayat.update', $riwayat->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                {{-- Jenis Pelanggaran --}}
                <x-forms.select 
                    name="jenis_pelanggaran_id" 
                    label="Jenis Pelanggaran" 
                    required 
                    :selected="$riwayat->jenis_pelanggaran_id"
                >
                    @foreach($jenisPelanggaran ?? [] as $p)
                        <option value="{{ $p->id }}" {{ old('jenis_pelanggaran_id', $riwayat->jenis_pelanggaran_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->nama_pelanggaran }} ({{ $p->poin }} poin)
                        </option>
                    @endforeach
                </x-forms.select>
                
                {{-- Tanggal & Waktu --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-forms.date 
                        name="tanggal_kejadian" 
                        label="Tanggal Kejadian" 
                        :value="\Carbon\Carbon::parse($riwayat->tanggal_kejadian)->format('Y-m-d')"
                        required 
                    />
                    
                    <x-forms.input 
                        type="time" 
                        name="waktu_kejadian" 
                        label="Waktu Kejadian" 
                        :value="\Carbon\Carbon::parse($riwayat->tanggal_kejadian)->format('H:i')"
                    />
                </div>
                
                {{-- Keterangan --}}
                <x-forms.textarea 
                    name="keterangan" 
                    label="Keterangan" 
                    :value="$riwayat->keterangan" 
                />
                
                {{-- Bukti Foto --}}
                <div class="form-group">
                    <label for="bukti_foto" class="form-label">Bukti Foto</label>
                    @if($riwayat->bukti_foto_path)
                        <div class="mb-3">
                            <img src="{{ Storage::url($riwayat->bukti_foto_path) }}" alt="Bukti" class="w-32 h-32 object-cover rounded-lg border">
                            <p class="text-sm text-gray-500 mt-1">Foto saat ini</p>
                        </div>
                    @endif
                    <input type="file" id="bukti_foto" name="bukti_foto" accept="image/*"
                           class="form-input @error('bukti_foto') error @enderror">
                    <p class="form-help">Biarkan kosong jika tidak ingin mengubah foto.</p>
                    @error('bukti_foto')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="save" size="18" />
                        <span>Simpan Perubahan</span>
                    </button>
                    <a href="{{ route('riwayat.index') }}" class="btn btn-secondary">
                        <x-ui.icon name="x" size="18" />
                        <span>Batal</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
