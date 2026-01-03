{{-- Single Delete Siswa Modal --}}
<div class="modal fade" id="deleteSingleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl rounded-2xl overflow-hidden">
            <form id="deleteSingleForm" method="POST">
                @csrf
                @method('DELETE')
                
                {{-- Header Danger Modern --}}
                <div class="bg-rose-600 p-6 text-center text-white">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-white/30">
                        <i class="fas fa-user-minus text-2xl"></i>
                    </div>
                    <h5 class="text-lg font-black uppercase tracking-widest m-0">Hapus Siswa</h5>
                    <p class="text-rose-100 text-[10px] mt-1 opacity-80 uppercase font-bold tracking-tighter">Penghapusan Data Individu</p>
                </div>
                
                <div class="modal-body p-8">
                    {{-- Nama Siswa Focus --}}
                    <div class="text-center mb-6">
                        <p class="text-slate-500 text-xs uppercase font-bold tracking-widest mb-1">Anda akan menghapus:</p>
                        <h4 class="text-slate-800 font-black text-lg m-0" id="deleteSiswaName"> Nama Siswa </h4>
                    </div>

                    <div class="space-y-5">
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
                            <textarea name="keterangan_keluar" class="custom-input-clean w-full resize-none" rows="3" 
                                      placeholder="Contoh: Lulus tahun 2024, pindah domisili, dll..."></textarea>
                        </div>
                    </div>

                    {{-- Info Singkat --}}
                    <div class="mt-6 flex items-center gap-2 text-[10px] text-slate-400 italic">
                        <i class="fas fa-info-circle"></i>
                        <span>Siswa akan dipindahkan ke folder "Data Terhapus"</span>
                    </div>
                </div>
                
                {{-- Footer Aksi --}}
                <div class="px-8 py-6 bg-slate-50/80 backdrop-blur-sm border-t border-slate-100 flex items-center gap-4">
                    <button type="button" 
                            class="flex-1 px-6 py-3 rounded-xl bg-white border border-slate-200 text-slate-500 text-[11px] font-black uppercase tracking-[0.1em] hover:bg-slate-50 hover:text-slate-700 hover:border-slate-300 transition-all duration-200 active:scale-95" 
                            data-dismiss="modal">
                        Batal
                    </button>
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

<script>
function openDeleteModal(siswaId, namaSiswa) {
    document.getElementById('deleteSiswaName').textContent = namaSiswa;
    document.getElementById('deleteSingleForm').action = `/siswa/${siswaId}`;
    $('#deleteSingleModal').modal('show');
}
</script>

<style>
    /* Sinkronisasi Style agar presisi dengan modal Bulk */
    #deleteSingleModal .custom-select-clean, 
    #deleteSingleModal .custom-input-clean {
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
    #deleteSingleModal textarea.custom-input-clean {
        height: auto;
        padding: 0.75rem 1rem;
    }
    #deleteSingleModal .custom-select-clean:focus, 
    #deleteSingleModal .custom-input-clean:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .active\:scale-95:active {
        transform: scale(0.97);
    }
</style>