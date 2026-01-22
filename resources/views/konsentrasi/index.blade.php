@extends('layouts.app')

@section('title', 'Manajemen Konsentrasi Keahlian')

@section('page-header')
    <x-page-header 
        title="Manajemen Konsentrasi" 
        subtitle="Kelola data konsentrasi keahlian per jurusan."
    >
        <x-slot:actions>
            <button 
                type="button" 
                @click="$dispatch('open-konsentrasi-form', { title: 'Tambah Konsentrasi Baru' })"
                class="btn btn-primary"
            >
                <x-ui.icon name="plus" size="18" />
                <span>Tambah Konsentrasi</span>
            </button>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
@php
    $tableConfig = [
        'endpoint' => route('konsentrasi.index'),
        'filters' => [
            'search' => request('search'),
            'jurusan_id' => request('jurusan_id')
        ],
        'containerId' => 'konsentrasi-table-container'
    ];
@endphp

<div class="space-y-4" x-data='dataTable(@json($tableConfig))'>
    
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Unified Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <x-ui.action-bar :total="$konsentrasiList->total()" totalLabel="Konsentrasi" class="!gap-4">
                <x-slot:search>
                    <input 
                        type="text" 
                        x-model.debounce.500ms="filters.search"
                        class="w-full md:w-80 rounded-xl border-0 bg-gray-100/80 text-sm text-gray-800 py-2.5 pl-10 pr-4 hover:bg-gray-100 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:shadow-lg focus:shadow-indigo-500/5 transition-all duration-200 placeholder-gray-400"
                        placeholder="Cari nama/kode konsentrasi..."
                    >
                </x-slot:search>
                <x-slot:filters>
                    <div class="space-y-3">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Jurusan</label>
                        <select x-model="filters.jurusan_id" class="form-select w-full text-sm">
                            <option value="">Semua Jurusan</option>
                            @foreach($jurusanList as $jurusan)
                                <option value="{{ $jurusan->id }}">{{ $jurusan->nama_jurusan }}</option>
                            @endforeach
                        </select>
                    </div>
                </x-slot:filters>
                <x-slot:reset>
                    <button type="button" @click="resetFilters()" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Reset</button>
                </x-slot:reset>
            </x-ui.action-bar>
        </div>

        {{-- Table Container for AJAX --}}
        <div id="konsentrasi-table-container" :class="{ 'opacity-50': isLoading }">
            @include('konsentrasi._table')
        </div>
    </div>
</div>

{{-- Slide-over Form Drawer --}}
<x-ui.slide-over 
    id="konsentrasi-form" 
    title="Tambah Konsentrasi" 
    size="lg"
    icon="layers"
>
    <form 
        id="konsentrasi-form-element"
        action="{{ route('konsentrasi.store') }}" 
        method="POST" 
        class="space-y-5"
        x-data="{
            editMode: false,
            editId: null,
            formData: {
                jurusan_id: '',
                kode_konsentrasi: '',
                nama_konsentrasi: '',
                deskripsi: '',
                is_active: true
            },
            resetForm() {
                this.editMode = false;
                this.editId = null;
                this.formData = {
                    jurusan_id: '',
                    kode_konsentrasi: '',
                    nama_konsentrasi: '',
                    deskripsi: '',
                    is_active: true
                };
            }
        }"
        x-on:open-konsentrasi-form.window="
            if ($event.detail?.editMode) {
                editMode = true;
                editId = $event.detail.id;
                formData.jurusan_id = $event.detail.jurusan_id;
                formData.kode_konsentrasi = $event.detail.kode_konsentrasi || '';
                formData.nama_konsentrasi = $event.detail.nama_konsentrasi || '';
                formData.deskripsi = $event.detail.deskripsi || '';
                formData.is_active = $event.detail.is_active;
            } else {
                resetForm();
            }
        "
        x-on:slide-over-closed.window="resetForm()"
        :action="editMode ? '{{ url('konsentrasi') }}/' + editId : '{{ route('konsentrasi.store') }}'"
    >
        @csrf
        <input type="hidden" name="_method" x-bind:value="editMode ? 'PUT' : 'POST'">
        
        <div class="form-section">
            <div class="form-section-title">
                <x-ui.icon name="info" size="14" />
                Informasi Konsentrasi
            </div>
            
            <div class="form-group">
                <label for="jurusan_id" class="form-label form-label-required">Jurusan Induk</label>
                <select 
                    name="jurusan_id" 
                    id="jurusan_id" 
                    class="form-input form-select" 
                    required
                    x-model="formData.jurusan_id"
                >
                    <option value="">-- Pilih Jurusan --</option>
                    @foreach($jurusanList as $j)
                        <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                    @endforeach
                </select>
                <p class="form-help">Konsentrasi akan menjadi bagian dari jurusan ini</p>
            </div>
            
            <x-forms.grid :cols="2">
                <div class="form-group">
                    <label for="kode_konsentrasi" class="form-label">Kode Konsentrasi</label>
                    <input 
                        type="text" 
                        name="kode_konsentrasi" 
                        id="kode_konsentrasi" 
                        class="form-input"
                        placeholder="Contoh: TPB"
                        maxlength="20"
                        x-model="formData.kode_konsentrasi"
                    >
                    <p class="form-help">Kode singkat (opsional)</p>
                </div>
                
                <div class="form-group">
                    <label for="nama_konsentrasi" class="form-label form-label-required">Nama Konsentrasi</label>
                    <input 
                        type="text" 
                        name="nama_konsentrasi" 
                        id="nama_konsentrasi" 
                        class="form-input"
                        placeholder="Contoh: Teknik Pembangkit"
                        required
                        x-model="formData.nama_konsentrasi"
                    >
                </div>
            </x-forms.grid>
            
            <div class="form-group">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea 
                    name="deskripsi" 
                    id="deskripsi" 
                    rows="2"
                    class="form-input"
                    placeholder="Deskripsi singkat tentang konsentrasi ini..."
                    x-model="formData.deskripsi"
                ></textarea>
            </div>
            
            <div class="form-group">
                <label class="flex items-start gap-3 cursor-pointer p-3 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-100 transition-colors">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1"
                        class="w-4 h-4 mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-offset-0"
                        x-model="formData.is_active"
                    >
                    <div class="flex-1">
                        <span class="text-sm font-medium text-blue-800">Konsentrasi Aktif</span>
                        <p class="text-xs text-blue-600 mt-0.5">Konsentrasi tidak aktif tidak muncul di dropdown</p>
                    </div>
                </label>
            </div>
        </div>
    </form>
    
    <x-slot:footer>
        <button type="button" @click="$dispatch('close-konsentrasi-form')" class="btn btn-secondary">
            Batal
        </button>
        <button 
            type="submit" 
            class="btn btn-primary"
            onclick="document.getElementById('konsentrasi-form-element').submit()"
        >
            <x-ui.icon name="save" size="18" />
            <span x-text="document.getElementById('konsentrasi-form-element')?.__x?.$data?.editMode ? 'Simpan Perubahan' : 'Simpan Konsentrasi'">Simpan Konsentrasi</span>
        </button>
    </x-slot:footer>
</x-ui.slide-over>
@endsection
