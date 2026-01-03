<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;

use App\Services\Rules\RulesEngineSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RulesEngineSettingsController extends Controller
{
    protected RulesEngineSettingsService $settingsService;

    public function __construct(RulesEngineSettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Display settings page (hanya untuk Operator)
     */
    public function index()
    {
        $settings = $this->settingsService->getAllGrouped();
        
        return view('rules-engine-settings.index', compact('settings'));
    }

    /**
     * Update settings (bulk update)
     */
    public function update(Request $request)
    {
        try {
            // Validate consistency first
            $consistencyErrors = $this->settingsService->validateConsistency($request->all());
            
            if (!empty($consistencyErrors)) {
                return back()
                    ->withErrors($consistencyErrors)
                    ->withInput()
                    ->with('error', 'Terdapat kesalahan validasi konsistensi. Silakan periksa kembali.');
            }

            // Bulk update
            $this->settingsService->bulkUpdate($request->all(), Auth::id());

            return redirect()
                ->route('rules-engine-settings.index')
                ->with('success', 'Pengaturan Rules Engine berhasil diperbarui');

        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Terdapat kesalahan validasi. Silakan periksa kembali.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Reset single setting to default
     */
    public function reset(Request $request, string $key)
    {
        try {
            $this->settingsService->reset($key, Auth::id());

            return redirect()
                ->route('rules-engine-settings.index')
                ->with('success', "Setting '{$key}' berhasil direset ke nilai default");

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Reset all settings to default
     */
    public function resetAll()
    {
        try {
            $this->settingsService->resetAll(Auth::id());

            return redirect()
                ->route('rules-engine-settings.index')
                ->with('success', 'Semua pengaturan berhasil direset ke nilai default');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get setting history (AJAX)
     */
    public function history(string $key)
    {
        try {
            $history = $this->settingsService->getHistory($key, 20);

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview impact of changes (AJAX)
     */
    public function preview(Request $request)
    {
        try {
            // Get current values
            $current = $this->settingsService->getAll();
            $currentMap = collect($current)->keyBy('key')->toArray();

            // Merge with proposed changes
            $proposed = $request->all();
            
            $comparison = [];
            foreach ($proposed as $key => $newValue) {
                if (isset($currentMap[$key])) {
                    $comparison[] = [
                        'key' => $key,
                        'label' => $currentMap[$key]['label'],
                        'current' => $currentMap[$key]['value'],
                        'proposed' => $newValue,
                        'changed' => $currentMap[$key]['value'] != $newValue
                    ];
                }
            }

            // Validate consistency
            $errors = $this->settingsService->validateConsistency($proposed);

            return response()->json([
                'success' => true,
                'comparison' => $comparison,
                'errors' => $errors,
                'hasErrors' => !empty($errors)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}



