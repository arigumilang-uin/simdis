@extends('layouts.app')

@section('title', 'Kenaikan / Pindah Kelas')
@section('subtitle', 'Pindahkan siswa ke kelas lain tanpa menghapus data riwayat.')
@section('page-header', true)

@section('actions')
    <a href="{{ route('siswa.index') }}" class="btn btn-secondary">
        <x-ui.icon name="arrow-left" size="18" />
        <span>Kembali ke Data Siswa</span>
    </a>
@endsection

@section('content')
<div class="max-w-6xl space-y-6" x-data="transferPage()">
    
    {{-- Info Banner --}}
    <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-xl">
        <div class="flex items-start gap-3">
            <x-ui.icon name="info" size="20" class="text-blue-500 shrink-0 mt-0.5" />
            <div>
                <p class="text-sm text-blue-800 font-medium">Fitur Kenaikan / Pindah Kelas</p>
                <p class="text-sm text-blue-700 mt-1">
                    Gunakan fitur ini untuk memindahkan siswa ke kelas lain saat kenaikan kelas, pindah konsentrasi, atau perubahan lainnya.
                    <strong>Semua data riwayat pelanggaran, pembinaan, dan wali murid akan tetap terjaga.</strong>
                </p>
            </div>
        </div>
    </div>

    {{-- Step 1: Pilih Kelas Asal --}}
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center text-sm font-bold">1</div>
                <span class="card-title">Pilih Kelas Asal</span>
            </div>
            <template x-if="isLoading">
                <div class="flex items-center gap-2 text-gray-500 text-sm">
                    <x-ui.icon name="spinner" size="16" class="animate-spin" />
                    <span>Memuat data siswa...</span>
                </div>
            </template>
        </div>
        <div class="card-body">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="form-group flex-1 min-w-[250px]">
                    <label class="form-label form-label-required">Kelas Asal</label>
                    <select x-model="selectedKelasId" @change="loadSiswa()" class="form-input form-select">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($allKelas as $kelas)
                            <option value="{{ $kelas->id }}">
                                {{ $kelas->nama_kelas }} - {{ $kelas->nama_jurusan ?? 'Umum' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="form-help">Pilih kelas untuk melihat daftar siswa yang bisa dipindahkan.</p>
                </div>
            </div>

            {{-- Kelas Info (shown after selection) --}}
            <template x-if="kelasInfo">
                <div class="mt-4 p-4 bg-gray-50 rounded-xl" x-transition>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Kelas:</span>
                            <span class="font-bold text-gray-800 ml-1" x-text="kelasInfo.nama_kelas"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Jurusan:</span>
                            <span class="font-medium text-gray-800 ml-1" x-text="kelasInfo.jurusan"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Wali Kelas:</span>
                            <span class="font-medium text-gray-800 ml-1" x-text="kelasInfo.wali_kelas"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Jumlah Siswa:</span>
                            <span class="font-bold text-primary-600 ml-1" x-text="kelasInfo.jumlah_siswa"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Step 2 & 3: Form (shown after kelas selected and has students) --}}
    <template x-if="siswaList.length > 0">
        <form method="POST" action="{{ route('siswa.bulk-transfer') }}" id="transfer-form">
            @csrf
            
            {{-- Step 2: Pilih Siswa --}}
            <div class="card">
                <div class="card-header">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center text-sm font-bold">2</div>
                        <span class="card-title">Pilih Siswa yang Akan Dipindahkan</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500">Terpilih:</span>
                        <span class="badge badge-primary" x-text="selected.length + ' siswa'"></span>
                        <button type="button" @click="toggleSelectAll()" class="btn btn-sm btn-secondary">
                            <span x-text="selected.length === siswaList.length ? 'Batal Pilih' : 'Pilih Semua'"></span>
                        </button>
                    </div>
                </div>
                <div class="table-container !rounded-none !border-0">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="w-12">
                                    <input type="checkbox" 
                                           @change="toggleSelectAll()" 
                                           :checked="selected.length === siswaList.length && siswaList.length > 0"
                                           class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                </th>
                                <th class="w-12">No</th>
                                <th>NISN</th>
                                <th>Nama Siswa</th>
                                <th>Kontak Wali</th>
                                <th class="text-center">Total Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(siswa, index) in siswaList" :key="siswa.id">
                                <tr :class="{ 'bg-primary-50/50': selected.includes(siswa.id) }">
                                    <td>
                                        <input type="checkbox" 
                                               name="siswa_ids[]" 
                                               :value="siswa.id"
                                               x-model.number="selected"
                                               class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    </td>
                                    <td class="text-gray-500" x-text="index + 1"></td>
                                    <td>
                                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded-md" x-text="siswa.nisn"></span>
                                    </td>
                                    <td class="font-medium text-gray-800" x-text="siswa.nama_siswa"></td>
                                    <td class="text-gray-500 text-sm" x-text="siswa.nomor_hp_wali_murid"></td>
                                    <td class="text-center">
                                        <span class="badge" 
                                              :class="siswa.total_poin > 50 ? 'badge-danger' : (siswa.total_poin > 20 ? 'badge-warning' : 'badge-success')"
                                              x-text="siswa.total_poin"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Step 3: Pilih Kelas Tujuan --}}
            <div class="card mt-6">
                <div class="card-header">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center text-sm font-bold">3</div>
                        <span class="card-title">Pilih Kelas Tujuan</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label class="form-label form-label-required">Kelas Tujuan</label>
                            <select name="target_kelas_id" x-model="targetKelasId" class="form-input form-select" required>
                                <option value="">-- Pilih Kelas Tujuan --</option>
                                @foreach($allKelas as $kelas)
                                    <option value="{{ $kelas->id }}" x-bind:disabled="selectedKelasId == {{ $kelas->id }}">
                                        {{ $kelas->nama_kelas }} - {{ $kelas->nama_jurusan ?? 'Umum' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="form-help">Kelas asal tidak dapat dipilih sebagai tujuan.</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Konfirmasi</label>
                            <label class="flex items-start gap-3 p-4 bg-amber-50 rounded-xl border border-amber-200 cursor-pointer">
                                <input type="checkbox" name="confirm_transfer" value="1" x-model="confirmed" 
                                       class="mt-0.5 w-5 h-5 text-amber-600 border-amber-300 rounded focus:ring-amber-500">
                                <div>
                                    <span class="font-medium text-amber-800">Saya yakin ingin memindahkan siswa terpilih</span>
                                    <p class="text-xs text-amber-600 mt-1">
                                        Data riwayat pelanggaran, pembinaan, dan wali murid akan tetap terhubung dengan siswa.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-4 mt-6">
                <a href="{{ route('siswa.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" 
                        class="btn btn-primary"
                        :disabled="selected.length === 0 || !targetKelasId || !confirmed"
                        :class="{ 'opacity-50 cursor-not-allowed': selected.length === 0 || !targetKelasId || !confirmed }">
                    <x-ui.icon name="arrow-right" size="18" />
                    <span>Pindahkan <span x-text="selected.length"></span> Siswa</span>
                </button>
            </div>
        </form>
    </template>

    {{-- Empty State: No class selected --}}
    <template x-if="!selectedKelasId && !isLoading">
        <div class="card">
            <div class="card-body">
                <x-ui.empty-state 
                    icon="arrow-up" 
                    title="Pilih Kelas Asal" 
                    description="Pilih kelas di atas untuk melihat daftar siswa yang bisa dipindahkan." 
                />
            </div>
        </div>
    </template>

    {{-- Empty State: Class selected but no students --}}
    <template x-if="selectedKelasId && siswaList.length === 0 && !isLoading && kelasInfo">
        <div class="card">
            <div class="card-body">
                <x-ui.empty-state 
                    icon="users" 
                    title="Tidak Ada Siswa" 
                    description="Kelas ini tidak memiliki siswa aktif." 
                />
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function transferPage() {
    return {
        selectedKelasId: '',
        targetKelasId: '',
        confirmed: false,
        isLoading: false,
        kelasInfo: null,
        siswaList: [],
        selected: [],
        
        async loadSiswa() {
            if (!this.selectedKelasId) {
                this.kelasInfo = null;
                this.siswaList = [];
                this.selected = [];
                return;
            }
            
            this.isLoading = true;
            this.selected = [];
            this.targetKelasId = '';
            this.confirmed = false;
            
            try {
                const response = await fetch(`{{ route('siswa.transfer.siswa') }}?kelas_id=${this.selectedKelasId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.kelasInfo = data.kelas;
                    this.siswaList = data.siswa;
                } else {
                    console.error('Error loading siswa:', data.message);
                    this.kelasInfo = null;
                    this.siswaList = [];
                }
            } catch (error) {
                console.error('Error fetching siswa:', error);
                this.kelasInfo = null;
                this.siswaList = [];
            } finally {
                this.isLoading = false;
            }
        },
        
        toggleSelectAll() {
            if (this.selected.length === this.siswaList.length) {
                this.selected = [];
            } else {
                this.selected = this.siswaList.map(s => s.id);
            }
        }
    }
}
</script>
@endpush
@endsection
