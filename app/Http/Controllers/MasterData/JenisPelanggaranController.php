<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Data\MasterData\JenisPelanggaranData;
use App\Http\Requests\MasterData\CreateJenisPelanggaranRequest;
use App\Http\Requests\MasterData\UpdateJenisPelanggaranRequest;
use App\Services\MasterData\JenisPelanggaranService;
use Illuminate\Http\Request;

/**
 * JenisPelanggaranController
 *
 * REFACTORED: 2025-12-11
 * PATTERN: Clean Architecture (Thin Controller)
 * RESPONSIBILITY: HTTP Request/Response ONLY
 * 
 * ALL business logic delegated to:
 * - JenisPelanggaranService (business logic)
 * - JenisPelanggaranRepository (data access)
 * - CreateJenisPelanggaranRequest/UpdateJenisPelanggaranRequest (validation)
 * 
 * BEFORE: 124 lines with mixed concerns
 * AFTER: ~80 lines, clean separation
 */
class JenisPelanggaranController extends Controller
{
    public function __construct(
        private JenisPelanggaranService $jenisPelanggaranService
    ) {}

    /**
     * Redirect to frequency-rules.index (consolidated page)
     */
    public function index()
    {
        return redirect()->route('frequency-rules.index');
    }

    /**
     * Tampilkan form create jenis pelanggaran
     * 
     * REFACTORED: Simple delegation
     */
    public function create()
    {
        $data = $this->jenisPelanggaranService->getDataForCreate();
        
        return view('jenis_pelanggaran.create', $data);
    }

    /**
     * Simpan jenis pelanggaran baru
     * 
     * REFACTORED from 21 lines to 12 lines
     * ALL logic moved to Service
     */
    public function store(CreateJenisPelanggaranRequest $request)
    {
        $jenisPelanggaranData = JenisPelanggaranData::from($request->validated());
        
        $jenisPelanggaran = $this->jenisPelanggaranService->createJenisPelanggaran($jenisPelanggaranData);

        // Redirect ke halaman kelola rules untuk pelanggaran yang baru dibuat
        return redirect()
            ->route('frequency-rules.show', $jenisPelanggaran->id)
            ->with('success', 'Jenis pelanggaran berhasil ditambahkan! Silakan atur frequency rules atau biarkan kosong untuk menggunakan poin default.');
    }

    /**
     * Tampilkan form edit jenis pelanggaran
     * 
     * REFACTORED: Simple delegation
     */
    public function edit($id)
    {
        $data = $this->jenisPelanggaranService->getDataForEdit($id);
        
        return view('jenis_pelanggaran.edit', $data);
    }

    /**
     * Perbarui jenis pelanggaran
     * 
     * REFACTORED from 19 lines to 13 lines
     * ALL logic moved to Service
     */
    public function update(UpdateJenisPelanggaranRequest $request, $id)
    {
        $jenisPelanggaranData = JenisPelanggaranData::from($request->validated());
        
        $this->jenisPelanggaranService->updateJenisPelanggaran($id, $jenisPelanggaranData);

        // Redirect kembali ke frequency rules
        return redirect()
            ->route('frequency-rules.show', $id)
            ->with('success', 'Jenis pelanggaran berhasil diperbarui!');
    }

    /**
     * Hapus jenis pelanggaran dengan proteksi
     * 
     * REFACTORED from 12 lines to 12 lines (same but cleaner)
     * Logic moved to Service
     */
    public function destroy($id)
    {
        $result = $this->jenisPelanggaranService->deleteJenisPelanggaran($id);
        
        if ($result['success']) {
            return redirect()
                ->route('frequency-rules.index')
                ->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }
}
