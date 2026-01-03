{{-- Bulk Delete Siswa Modal Component --}}
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl rounded-2xl overflow-hidden">
            <form action="{{ route('siswa.bulk-delete') }}" method="POST" 
                  onsubmit="return confirm('⚠️ YAKIN menghapus SEMUA siswa di kelas ini?\n\nTindakan ini dapat memengaruhi riwayat data terkait!')">
                @csrf
                
                {{-- Header dengan gaya Danger Modern --}}
                <div class="bg-rose-600 p-6 text-center text-white">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-white/30">
                        <i class="fas fa-trash-alt text-2xl"></i>
                    </div>
                    <h5 class="text-lg font-black uppercase tracking-widest m-0">Hapus Per Kelas</h5>
                    <p class="text-rose-100 text-xs mt-1 opacity-80">Proses penghapusan massal data siswa</p>
                </div>
                
                <div class="modal-body p-8">
                    {{-- Info Alert --}}
                    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                            <div>
                                <h6 class="text-xs font-bold text-blue-800 uppercase tracking-tight mb-1">Informasi Penting</h6>
                                <p class="text-[11px] text-blue-700 leading-relaxed m-0">
                                    Siswa akan di-<strong>soft delete</strong>. Data riwayat pelanggaran dan tindak lanjut akan diarsipkan. Anda masih bisa memulihkan data ini melalui menu <strong>Data Terhapus</strong>.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-5">
                        {{-- Kelas Selection --}}
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-tight">Pilih Kelas Target <span class="text-rose-500">*</span></label>
                            <select name="kelas_id" class="custom-select-clean w-full" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($allKelas as $k)
                                    @php
                                        $siswaCount = \App\Models\Siswa::where('kelas_id', $k->id)->count();
                                    @endphp
                                    <option value="{{ $k->id }}" {{ $siswaCount == 0 ? 'disabled' : '' }}>
                                        {{ $k->nama_kelas }} ({{ $siswaCount }} siswa aktif)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Alasan Keluar --}}
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-tight">Alasan Keluar <span class="text-rose-500">*</span></label>
                            <select name="alasan_keluar" class="custom-select-clean w-full" required>
                                <option value="">-- Pilih Alasan --</option>
                                <option value="Alumni">🎓 Alumni (Lulus)</option>
                                <option value="Dikeluarkan">🚪 Dikeluarkan (Drop Out)</option>
                                <option value="Pindah Sekolah">🏫 Pindah Sekolah</option>
                                <option value="Lainnya">❓ Lainnya</option>
                            </select>
                        </div>
                        
                        {{-- Keterangan Keluar --}}
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-tight">Keterangan (Opsional)</label>
                            <textarea name="keterangan_keluar" class="custom-input-clean w-full resize-none" rows="2" 
                                      placeholder="Contoh: Lulus tahun ajaran 2024/2025..."></textarea>
                        </div>
                        
                        {{-- Options Section - Refined Precision --}}
                        <div class="space-y-4 pt-4 border-t border-slate-50">
                            <label class="flex items-center gap-3 cursor-pointer group m-0 p-1">
                                <div class="relative flex items-center">
                                    <input type="checkbox" name="delete_orphaned_wali" value="1" 
                                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20 transition-all cursor-pointer">
                                </div>
                                <span class="text-[11px] text-slate-600 font-semibold leading-none group-hover:text-indigo-600 transition-colors">
                                    Hapus juga akun Wali Murid yang tidak lagi memiliki siswa aktif
                                </span>
                            </label>
                            
                            <label class="flex items-center gap-3 cursor-pointer group m-0 p-1">
                                <div class="relative flex items-center">
                                    <input type="checkbox" name="confirm" value="1" required 
                                        class="w-4 h-4 rounded border-rose-300 text-rose-600 focus:ring-rose-500/20 transition-all cursor-pointer">
                                </div>
                                <span class="text-[11px] text-rose-700 font-black leading-none uppercase tracking-wider">
                                    Saya sadar tindakan ini memproses data dalam jumlah besar
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                
                {{-- Footer Aksi - Refined Version --}}
                <div class="px-8 py-6 bg-slate-50/80 backdrop-blur-sm border-t border-slate-100 flex items-center gap-4">
                    {{-- Tombol Batal: Lebih Low-Profile namun tetap Clickable --}}
                    <button type="button" 
                            class="flex-1 px-6 py-3 rounded-xl bg-white border border-slate-200 text-slate-500 text-[11px] font-black uppercase tracking-[0.1em] hover:bg-slate-50 hover:text-slate-700 hover:border-slate-300 transition-all duration-200 active:scale-95" 
                            data-dismiss="modal">
                        Batal
                    </button>

                    {{-- Tombol Hapus: Dominan, Bold, dan Memberikan Kesan Warning --}}
                    <button type="submit" 
                            class="flex-[1.5] flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-rose-600 text-white text-[11px] font-black uppercase tracking-[0.1em] shadow-lg shadow-rose-200 hover:bg-rose-700 hover:shadow-rose-300 transition-all duration-200 active:scale-95 border-none">
                        <i class="fas fa-trash-alt text-[10px] opacity-80"></i>
                        <span>Konfirmasi Hapus</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Sinkronisasi Style Modal dengan Komponen Dashboard */
    #bulkDeleteModal .custom-select-clean, 
    #bulkDeleteModal .custom-input-clean {
        height: 46px;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0 1rem;
        font-size: 0.85rem;
        background-color: #ffffff;
        color: #1e293b;
        transition: all 0.2s ease;
        outline: none;
    }
    #bulkDeleteModal textarea.custom-input-clean {
        height: auto;
        padding: 0.75rem 1rem;
    }
    #bulkDeleteModal .custom-select-clean:focus, 
    #bulkDeleteModal .custom-input-clean:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    /* Modal Backdrop Fix */
    .modal-backdrop {
        opacity: 0.4 !important;
        background-color: #0f172a !important;
    }
</style>