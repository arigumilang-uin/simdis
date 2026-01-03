@extends('layouts.app')

@section('title', 'Tambah Aturan Baru')
@section('subtitle', 'Buat aturan kedisiplinan baru.')
@section('page-header', true)

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Jenis Pelanggaran</h3>
        </div>
        <div class="card-body">
            {{-- Workflow Info --}}
            <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 mb-6">
                <h4 class="font-semibold text-blue-800 mb-2">Workflow:</h4>
                <ol class="list-decimal list-inside text-sm text-blue-700 space-y-1">
                    <li>Isi form ini untuk membuat jenis pelanggaran baru</li>
                    <li>Setelah disimpan, Anda akan diarahkan ke halaman <strong>Kelola Rules</strong></li>
                    <li>Di halaman Kelola Rules, atur: <strong>Frekuensi, Poin, Sanksi, Trigger Surat, Pembina</strong></li>
                </ol>
            </div>
            
            <form action="{{ route('jenis-pelanggaran.store') }}" method="POST" class="space-y-6">
                @csrf
                
                {{-- Nama Pelanggaran --}}
                {{-- Nama Pelanggaran --}}
                <x-forms.input 
                    name="nama_pelanggaran" 
                    label="Nama Pelanggaran" 
                    placeholder="Contoh: Rambut tidak sesuai (3-2-1, diwarnai, crop)"
                    required autofocus 
                />
                
                {{-- Kategori Pelanggaran --}}
                {{-- Kategori Pelanggaran --}}
                <x-forms.select 
                    name="kategori_id" 
                    label="Kategori Pelanggaran" 
                    required 
                >
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($kategori ?? [] as $k)
                        <option value="{{ $k->id }}" {{ old('kategori_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kategori }}
                        </option>
                    @endforeach
                </x-forms.select>
                
                {{-- Filter Category --}}
                {{-- Filter Category --}}
                <x-forms.select 
                    name="filter_category" 
                    label="Filter Kategori (opsional)" 
                    helper="Filter untuk memudahkan pencarian saat catat pelanggaran." 
                >
                    <option value="">-- Tidak ada filter --</option>
                    <option value="atribut" {{ old('filter_category') == 'atribut' ? 'selected' : '' }}>Atribut/Seragam</option>
                    <option value="absensi" {{ old('filter_category') == 'absensi' ? 'selected' : '' }}>Absensi/Kehadiran</option>
                    <option value="kerapian" {{ old('filter_category') == 'kerapian' ? 'selected' : '' }}>Kerapian/Kebersihan</option>
                    <option value="ibadah" {{ old('filter_category') == 'ibadah' ? 'selected' : '' }}>Ibadah/Agama</option>
                    <option value="berat" {{ old('filter_category') == 'berat' ? 'selected' : '' }}>Berat/Kejahatan</option>
                </x-forms.select>
                
                {{-- Keywords --}}
                {{-- Keywords --}}
                <x-forms.textarea 
                    name="keywords" 
                    label="Alias / Keywords (opsional)" 
                    helper="Kata kunci alternatif untuk memudahkan pencarian. Pisahkan dengan koma atau enter." 
                    placeholder="Contoh: Rambut panjang, Rambut gondrong, Rambut dicat" 
                />
                
                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="save" size="18" />
                        <span>Simpan & Lanjut ke Kelola Rules</span>
                    </button>
                    <a href="{{ route('frequency-rules.index') }}" class="btn btn-secondary">
                        <x-ui.icon name="x" size="18" />
                        <span>Batal</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
