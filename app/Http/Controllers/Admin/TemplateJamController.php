<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateJam;
use App\Models\PeriodeSemester;
use App\Enums\Hari;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

/**
 * Template Jam Controller
 * 
 * Mengelola template jam pelajaran per periode dan per hari.
 * Dengan fitur: auto-generate, inline edit, dan time sync.
 */
class TemplateJamController extends Controller
{
    /**
     * Display template jam for a periode
     */
    public function index(Request $request): View
    {
        // Get all periods
        $allPeriodes = PeriodeSemester::orderByDesc('is_active')
            ->orderByDesc('tanggal_mulai')
            ->get();

        // Determine selected periode
        $periodeId = $request->input('periode_id');
        $selectedPeriode = null;
        
        if ($periodeId) {
            $selectedPeriode = PeriodeSemester::find($periodeId);
        }
        if (!$selectedPeriode) {
            $selectedPeriode = PeriodeSemester::current();
        }

        // Get selected hari (default: Senin)
        $selectedHari = $request->input('hari', 'Senin');
        
        // Get template jam for selected periode and hari
        $templateJams = collect();
        if ($selectedPeriode) {
            $templateJams = TemplateJam::forPeriode($selectedPeriode->id)
                ->forHari($selectedHari)
                ->ordered()
                ->get();
        }

        // Available hari
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        // Logic Dynamic Options:
        // 1. Default options
        $defaultTipes = ['pelajaran', 'istirahat', 'ishoma', 'upacara', 'lainnya'];
        
        // 2. Fetch custom types from DB (excluded defaults)
        $customTipes = TemplateJam::select('tipe')
            ->distinct()
            ->whereNotIn('tipe', $defaultTipes)
            ->pluck('tipe') // pluck value directly
            ->sort()
            ->values()
            ->all();

        // 3. Merge into options array
        $tipeOptions = [
            'pelajaran' => 'Pelajaran',
            'istirahat' => 'Istirahat',
            'ishoma' => 'Ishoma',
            'upacara' => 'Upacara',
        ];

        // Add custom types to options with capitalized labels
        foreach ($customTipes as $ct) {
            $tipeOptions[$ct] = ucfirst($ct);
        }

        // Add trigger option at the end
        $tipeOptions['lainnya'] = 'Lainnya (Tambah Baru...)';

        return view('admin.template-jam.index', [
            'allPeriodes' => $allPeriodes,
            'selectedPeriode' => $selectedPeriode,
            'selectedHari' => $selectedHari,
            'templateJams' => $templateJams,
            'hariList' => $hariList,
            'tipeOptions' => $tipeOptions,
        ]);
    }

    /**
     * Generate default template (15 slots)
     */
    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'periode_semester_id' => 'required|exists:periode_semester,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
        ]);

        // Check if already has template
        $existingCount = TemplateJam::forPeriode($validated['periode_semester_id'])
            ->forHari($validated['hari'])
            ->count();
            
        if ($existingCount > 0) {
            return redirect()
                ->route('admin.template-jam.index', [
                    'periode_id' => $validated['periode_semester_id'],
                    'hari' => $validated['hari'],
                ])
                ->with('error', 'Template untuk hari ini sudah ada.');
        }

        // Default template: 15 pelajaran + 2 istirahat + 1 ishoma
        $defaultTemplate = [
            ['tipe' => 'pelajaran', 'jam_mulai' => '07:00', 'jam_selesai' => '07:40'],
            ['tipe' => 'pelajaran', 'jam_mulai' => '07:40', 'jam_selesai' => '08:20'],
            ['tipe' => 'pelajaran', 'jam_mulai' => '08:20', 'jam_selesai' => '09:00'],
            ['tipe' => 'istirahat', 'jam_mulai' => '09:00', 'jam_selesai' => '09:15'],
            ['tipe' => 'pelajaran', 'jam_mulai' => '09:15', 'jam_selesai' => '09:55'],
            ['tipe' => 'pelajaran', 'jam_mulai' => '09:55', 'jam_selesai' => '10:35'],
            ['tipe' => 'pelajaran', 'jam_mulai' => '10:35', 'jam_selesai' => '11:15'],
            ['tipe' => 'pelajaran', 'jam_mulai' => '11:15', 'jam_selesai' => '11:55'],
            ['tipe' => 'ishoma', 'jam_mulai' => '11:55', 'jam_selesai' => '12:40'],
            ['tipe' => 'pelajaran', 'jam_mulai' => '12:40', 'jam_selesai' => '13:20'],
            ['tipe' => 'pelajaran', 'jam_mulai' => '13:20', 'jam_selesai' => '14:00'],
            ['tipe' => 'pelajaran', 'jam_mulai' => '14:00', 'jam_selesai' => '14:40'],
            ['tipe' => 'istirahat', 'jam_mulai' => '14:40', 'jam_selesai' => '14:55'],
            ['tipe' => 'pelajaran', 'jam_mulai' => '14:55', 'jam_selesai' => '15:35'],
            ['tipe' => 'pelajaran', 'jam_mulai' => '15:35', 'jam_selesai' => '16:15'],
            ['tipe' => 'pelajaran', 'jam_mulai' => '16:15', 'jam_selesai' => '16:55'],
            ['tipe' => 'pelajaran', 'jam_mulai' => '16:55', 'jam_selesai' => '17:35'],
        ];

        // Create slots
        foreach ($defaultTemplate as $urutan => $slot) {
            TemplateJam::create([
                'periode_semester_id' => $validated['periode_semester_id'],
                'hari' => $validated['hari'],
                'urutan' => $urutan + 1,
                'label' => $this->generateLabel($slot['tipe'], $urutan + 1),
                'jam_mulai' => $slot['jam_mulai'],
                'jam_selesai' => $slot['jam_selesai'],
                'tipe' => $slot['tipe'],
                'is_active' => true,
            ]);
        }

        return redirect()
            ->route('admin.template-jam.index', [
                'periode_id' => $validated['periode_semester_id'],
                'hari' => $validated['hari'],
            ])
            ->with('success', 'Template default berhasil di-generate.');
    }

    /**
     * Generate label based on tipe
     */
    private function generateLabel(string $tipe, int $urutan): string
    {
        return match($tipe) {
            'pelajaran' => 'Jam Pelajaran',
            'istirahat' => 'Istirahat',
            'ishoma' => 'Ishoma',
            'upacara' => 'Upacara',
            default => 'Lainnya',
        };
    }

    /**
     * Add a new row to the end
     */
    public function addRow(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'periode_semester_id' => 'required|exists:periode_semester,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jumlah' => 'nullable|integer|min:1|max:20',
        ]);

        $jumlahToAdd = $validated['jumlah'] ?? 1;

        // Get last slot initially
        $lastSlot = TemplateJam::forPeriode($validated['periode_semester_id'])
            ->forHari($validated['hari'])
            ->orderByDesc('urutan')
            ->first();

        // Initial reference points for loop
        $currentUrutan = $lastSlot?->urutan ?? 0;
        $currentJamMulai = $lastSlot ? $lastSlot->jam_selesai : '07:00';

        // Normalize time string
        if ($currentJamMulai instanceof \DateTime) {
            $currentJamMulai = $currentJamMulai->format('H:i');
        }

        // Loop insertion
        for ($i = 0; $i < $jumlahToAdd; $i++) {
            $currentUrutan++;

            // Calculate selesai time (40 mins duration)
            $selesaiTime = \Carbon\Carbon::createFromFormat('H:i', $currentJamMulai)->addMinutes(40);
            $currentJamSelesai = $selesaiTime->format('H:i');

            TemplateJam::create([
                'periode_semester_id' => $validated['periode_semester_id'],
                'hari' => $validated['hari'],
                'urutan' => $currentUrutan,
                'label' => 'Jam Pelajaran',
                'jam_mulai' => $currentJamMulai,
                'jam_selesai' => $currentJamSelesai,
                'tipe' => 'pelajaran',
                'is_active' => true,
            ]);

            // Set next mulai to current selesai
            $currentJamMulai = $currentJamSelesai;
        }

        return redirect()
            ->route('admin.template-jam.index', [
                'periode_id' => $validated['periode_semester_id'],
                'hari' => $validated['hari'],
            ])
            ->with('success', $jumlahToAdd . ' baris baru berhasil ditambahkan.');
    }



    /**
     * Update single field via AJAX
     */
    public function updateField(Request $request, int $id): JsonResponse
    {
        $templateJam = TemplateJam::find($id);
        
        if (!$templateJam) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan']);
        }

        $field = $request->input('field');
        $value = $request->input('value');

        // Validate field
        $allowedFields = ['jam_mulai', 'jam_selesai', 'tipe', 'is_active', 'label'];
        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Field tidak valid']);
        }

        // Update
        if ($field === 'is_active') {
            $value = (bool) $value;
        }

        $templateJam->update([$field => $value]);

        return response()->json(['success' => true]);
    }

    /**
     * Store new template jam (legacy)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'periode_semester_id' => 'required|exists:periode_semester,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'label' => 'required|string|max:50',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tipe' => 'required|string|max:50',
        ]);

        // Get next urutan
        $maxUrutan = TemplateJam::forPeriode($validated['periode_semester_id'])
            ->forHari($validated['hari'])
            ->max('urutan') ?? 0;
        
        $validated['urutan'] = $maxUrutan + 1;
        $validated['is_active'] = true;

        TemplateJam::create($validated);

        return redirect()
            ->route('admin.template-jam.index', [
                'periode_id' => $validated['periode_semester_id'],
                'hari' => $validated['hari'],
            ])
            ->with('success', 'Slot waktu berhasil ditambahkan.');
    }

    /**
     * Update template jam (legacy)
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $templateJam = TemplateJam::findOrFail($id);

        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tipe' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $templateJam->update($validated);

        return redirect()
            ->route('admin.template-jam.index', [
                'periode_id' => $templateJam->periode_semester_id,
                'hari' => $templateJam->hari->value ?? $templateJam->hari,
            ])
            ->with('success', 'Slot waktu berhasil diperbarui.');
    }

    /**
     * Delete template jam
     */
    public function destroy(int $id): RedirectResponse
    {
        $templateJam = TemplateJam::findOrFail($id);
        $periodeId = $templateJam->periode_semester_id;
        $hari = $templateJam->hari->value ?? $templateJam->hari;
        
        // Check if slot is used in jadwal
        if ($templateJam->jadwalMengajar()->exists()) {
            return redirect()
                ->route('admin.template-jam.index', [
                    'periode_id' => $periodeId,
                    'hari' => $hari,
                ])
                ->with('error', 'Tidak dapat menghapus slot yang sudah digunakan di jadwal mengajar.');
        }

        $templateJam->delete();

        // Reorder remaining slots
        $this->reorderSlots($periodeId, $hari);

        return redirect()
            ->route('admin.template-jam.index', [
                'periode_id' => $periodeId,
                'hari' => $hari,
            ])
            ->with('success', 'Slot waktu berhasil dihapus.');
    }

    /**
     * Reorder slot (move up/down)
     */
    public function reorder(Request $request, int $id): RedirectResponse
    {
        $templateJam = TemplateJam::findOrFail($id);
        $direction = $request->input('direction');
        
        $periodeId = $templateJam->periode_semester_id;
        $hari = $templateJam->hari->value ?? $templateJam->hari;
        
        $neighbor = null;
        
        if ($direction === 'up') {
            $neighbor = TemplateJam::forPeriode($periodeId)
                ->forHari($hari)
                ->where('urutan', '<', $templateJam->urutan)
                ->orderByDesc('urutan')
                ->first();
        } elseif ($direction === 'down') {
            $neighbor = TemplateJam::forPeriode($periodeId)
                ->forHari($hari)
                ->where('urutan', '>', $templateJam->urutan)
                ->orderBy('urutan')
                ->first();
        }

        if ($neighbor) {
            // Swap urutan
            $temp = $templateJam->urutan;
            $templateJam->urutan = $neighbor->urutan;
            $templateJam->save();
            
            $neighbor->urutan = $temp;
            $neighbor->save();
        }

        return redirect()
            ->route('admin.template-jam.index', [
                'periode_id' => $periodeId,
                'hari' => $hari,
            ]);
    }

    /**
     * Copy template from another periode
     */
    public function copy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_periode_id' => 'required|exists:periode_semester,id',
            'to_periode_id' => 'required|exists:periode_semester,id|different:from_periode_id',
        ]);

        // Check if target already has template
        $existingCount = TemplateJam::forPeriode($validated['to_periode_id'])->count();
        if ($existingCount > 0) {
            return redirect()
                ->route('admin.template-jam.index', ['periode_id' => $validated['to_periode_id']])
                ->with('error', 'Periode tujuan sudah memiliki template jam. Hapus terlebih dahulu jika ingin copy.');
        }

        $count = TemplateJam::copyFromPeriode(
            $validated['from_periode_id'],
            $validated['to_periode_id']
        );

        return redirect()
            ->route('admin.template-jam.index', ['periode_id' => $validated['to_periode_id']])
            ->with('success', "Berhasil menyalin {$count} slot waktu.");
    }

    /**
     * Helper: Reorder slots after deletion
     */
    private function reorderSlots(int $periodeId, string $hari): void
    {
        $slots = TemplateJam::forPeriode($periodeId)
            ->forHari($hari)
            ->orderBy('urutan')
            ->get();

        foreach ($slots as $index => $slot) {
            $slot->update(['urutan' => $index + 1]);
        }
    }
}
