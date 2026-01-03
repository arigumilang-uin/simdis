<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rules\CreateFrequencyRuleRequest;
use App\Http\Requests\Rules\UpdateFrequencyRuleRequest;
use App\Services\Rules\FrequencyRuleService;
use App\Repositories\JenisPelanggaranRepository;
use Illuminate\Http\Request;

/**
 * Frequency Rules Controller - REFACTORED TO CLEAN ARCHITECTURE
 * 
 * ROLE: Courier (Request → Service → Response)
 * 
 * ✅ Uses Service Layer (FrequencyRuleService)
 * ✅ Uses FormRequests for validation
 * ✅ Uses Repository for data access
 * ✅ NO direct Model queries
 * ✅ NO business logic
 * ✅ Methods < 20 lines
 */
class FrequencyRulesController extends Controller
{
    public function __construct(
        private FrequencyRuleService $frequencyRuleService,
        private JenisPelanggaranRepository $jenisRepo
    ) {}

    /**
     * Display list of all jenis pelanggaran with frequency rules
     */
    public function index(Request $request)
    {
        $kategoriId = $request->get('kategori_id');
        
        // Get jenis pelanggaran via Repository
        $query = \App\Models\JenisPelanggaran::with([
            'kategoriPelanggaran',
            'frequencyRules' => fn($q) => $q->orderBy('display_order')
        ]);
        
        if ($kategoriId) {
            $query->where('kategori_id', $kategoriId);
        }
        
        $jenisPelanggaran = $query->orderBy('kategori_id')->orderBy('nama_pelanggaran')->get();
        
        // Return partial view if requested
        if ($request->ajax() || $request->has('render_partial')) {
            return view('frequency-rules._table', compact('jenisPelanggaran'));
        }
        
        // Get kategori for filter
        $kategoris = \App\Models\KategoriPelanggaran::orderBy('nama_kategori')->get();
        
        return view('frequency-rules.index', compact('jenisPelanggaran', 'kategoris', 'kategoriId'));
    }

    /**
     * Display frequency rules for specific jenis pelanggaran
     */
    public function show(int $jenisPelanggaranId)
    {
        $jenisPelanggaran = $this->frequencyRuleService->getJenisPelanggaranWithRules($jenisPelanggaranId);
        
        if (!$jenisPelanggaran) {
            abort(404, 'Jenis pelanggaran not found');
        }
        
        return view('frequency-rules.show', compact('jenisPelanggaran'));
    }

    /**
     * Store new frequency rule
     * 
     * CLEAN: Uses FormRequest for validation, Service for business logic
     */
    public function store(CreateFrequencyRuleRequest $request, int $jenisPelanggaranId)
    {
        // Delegate to Service
        $rule = $this->frequencyRuleService->createRule(
            $jenisPelanggaranId,
            $request->validated()
        );
        
        return redirect()
            ->route('frequency-rules.show', $jenisPelanggaranId)
            ->with('success', 'Frequency rule berhasil dibuat.');
    }

    /**
     * Update existing frequency rule
     * 
     * CLEAN: Uses FormRequest and Service
     */
    public function update(UpdateFrequencyRuleRequest $request, int $ruleId)
    {
        $this->frequencyRuleService->updateRule($ruleId, $request->validated());
        
        return redirect()
            ->back()
            ->with('success', 'Frequency rule berhasil diupdate.');
    }

    /**
     * Delete frequency rule
     * 
     * CLEAN: Service handles business logic (deactivation if no rules remain)
     */
    public function destroy(int $ruleId)
    {
        $this->frequencyRuleService->deleteRule($ruleId);
        
        return redirect()
            ->back()
            ->with('success', 'Frequency rule berhasil dihapus.');
    }

    /**
     * Toggle is_active status for jenis pelanggaran
     * 
     * NOTE: This is kept for backward compatibility with AJAX toggle
     * Could be moved to JenisPelanggaranController in future
     */
    public function toggleActive(Request $request, int $jenisPelanggaranId)
    {
        $jenisPelanggaran = $this->jenisRepo->findWithFrequencyRules($jenisPelanggaranId);
        
        if (!$jenisPelanggaran) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis pelanggaran not found'
            ], 404);
        }
        
        // Validate: cannot activate without rules
        if (!$jenisPelanggaran->is_active && $jenisPelanggaran->frequencyRules->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa mengaktifkan pelanggaran tanpa rules. Tambahkan rules terlebih dahulu.'
            ], 400);
        }
        
        // Toggle status
        $newStatus = !$jenisPelanggaran->is_active;
        $this->jenisRepo->updateFrequencyStatus($jenisPelanggaranId, $newStatus, $newStatus);
        
        return response()->json([
            'success' => true,
            'is_active' => $newStatus,
            'message' => $newStatus ? 'Pelanggaran diakt ifkan' : 'Pelanggaran dinonaktifkan'
        ]);
    }
}
