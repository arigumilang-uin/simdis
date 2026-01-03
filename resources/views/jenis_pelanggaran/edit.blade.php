@extends('layouts.app')

@section('title', 'Edit Aturan')
@section('subtitle', $jenisPelanggaran->nama_pelanggaran ?? 'Edit Jenis Pelanggaran')
@section('page-header', true)

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Jenis Pelanggaran</h3>
        </div>
        <div class="card-body">
            {{-- Info Box --}}
            <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 mb-6">
                <div class="flex items-start gap-3">
                    <x-ui.icon name="info" size="20" class="text-blue-500 shrink-0 mt-0.5" />
                    <div class="text-sm text-blue-700">
                        <strong>Catatan:</strong> Untuk mengatur <strong>Poin, Sanksi, Trigger Surat, dan Pembina</strong>, silakan gunakan halaman <strong>"Kelola Rules"</strong>.
                        <a href="{{ route('frequency-rules.show', $jenisPelanggaran->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold underline block mt-2">
                            → Lanjut ke Halaman Kelola Rules
                        </a>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('jenis-pelanggaran.update', $jenisPelanggaran->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                {{-- Nama Pelanggaran --}}
                {{-- Nama Pelanggaran --}}
                <x-forms.input 
                    name="nama_pelanggaran" 
                    label="Nama Pelanggaran" 
                    :value="$jenisPelanggaran->nama_pelanggaran" 
                    required autofocus 
                />
                
                {{-- Kategori Pelanggaran --}}
                {{-- Kategori Pelanggaran --}}
                <x-forms.select 
                    name="kategori_id" 
                    label="Kategori Pelanggaran" 
                    required 
                    :selected="$jenisPelanggaran->kategori_id"
                >
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($kategori ?? [] as $k)
                        <option value="{{ $k->id }}" {{ old('kategori_id', $jenisPelanggaran->kategori_id) == $k->id ? 'selected' : '' }}>
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
                    <option value="atribut" {{ old('filter_category', $jenisPelanggaran->filter_category) == 'atribut' ? 'selected' : '' }}>Atribut/Seragam</option>
                    <option value="absensi" {{ old('filter_category', $jenisPelanggaran->filter_category) == 'absensi' ? 'selected' : '' }}>Absensi/Kehadiran</option>
                    <option value="kerapian" {{ old('filter_category', $jenisPelanggaran->filter_category) == 'kerapian' ? 'selected' : '' }}>Kerapian/Kebersihan</option>
                    <option value="ibadah" {{ old('filter_category', $jenisPelanggaran->filter_category) == 'ibadah' ? 'selected' : '' }}>Ibadah/Agama</option>
                    <option value="berat" {{ old('filter_category', $jenisPelanggaran->filter_category) == 'berat' ? 'selected' : '' }}>Berat/Kejahatan</option>
                </x-forms.select>
                
                {{-- Keywords --}}
                {{-- Keywords --}}
                <x-forms.textarea 
                    name="keywords" 
                    label="Alias / Keywords (opsional)" 
                    helper="Kata kunci alternatif untuk memudahkan pencarian." 
                    placeholder="Contoh: Rambut panjang, Rambut gondrong, Rambut dicat" 
                    :value="$jenisPelanggaran->keywords" 
                />
                
                {{-- Current Status --}}
                <div class="p-4 bg-gray-50 rounded-xl">
                    <h4 class="font-semibold text-gray-700 mb-3">Status Saat Ini</h4>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Poin Saat Ini</dt>
                            <dd class="font-bold text-lg text-amber-600">{{ $jenisPelanggaran->poin ?? 0 }} Poin</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Status</dt>
                            <dd>
                                @if($jenisPelanggaran->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-neutral">Nonaktif</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="save" size="18" />
                        <span>Simpan Perubahan</span>
                    </button>
                    <a href="{{ route('frequency-rules.show', $jenisPelanggaran->id) }}" class="btn btn-secondary">
                        <x-ui.icon name="settings" size="18" />
                        <span>Kelola Rules</span>
                    </a>
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
