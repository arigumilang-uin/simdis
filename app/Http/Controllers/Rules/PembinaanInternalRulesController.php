<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;

use App\Models\PembinaanInternalRule;
use Illuminate\Http\Request;

class PembinaanInternalRulesController extends Controller
{
    /**
     * Display list semua pembinaan internal rules.
     */
    public function index()
    {
        $rules = PembinaanInternalRule::orderBy('display_order')->get();
        
        // SMART DEFAULTS for new rule
        $suggestedPoinMin = 0;
        $suggestedDisplayOrder = 1;
        
        if ($rules->isNotEmpty()) {
            // Calculate suggested poin_min
            $highestMax = $rules->max('poin_max');
            
            if ($highestMax !== null) {
                // Recommended: Start from highest max + 1
                $suggestedPoinMin = $highestMax + 1;
            } else {
                // If highest rule is open-ended (no max), find the highest poin_min
                $highestMin = $rules->max('poin_min');
                
                // Check for gaps between rules
                $sortedRules = $rules->sortBy('poin_min');
                $largestGap = 0;
                $gapStart = 0;
                
                foreach ($sortedRules as $index => $rule) {
                    if ($index > 0) {
                        $prevRule = $sortedRules[$index - 1];
                        $prevMax = $prevRule->poin_max ?? PHP_INT_MAX;
                        $currentMin = $rule->poin_min;
                        
                        $gap = $currentMin - $prevMax - 1;
                        
                        if ($gap > $largestGap && $gap > 10) { // Only consider gaps > 10 points
                            $largestGap = $gap;
                            $gapStart = $prevMax + 1;
                        }
                    }
                }
                
                // If large gap found, suggest filling it; otherwise suggest after highest
                if ($largestGap > 10) {
                    $suggestedPoinMin = $gapStart;
                } else {
                    $suggestedPoinMin = $highestMin + 50; // Default offset
                }
            }
            
            // Suggested display order = max + 1
            $suggestedDisplayOrder = $rules->max('display_order') + 1;
        }
        
        return view('pembinaan-internal-rules.index', compact('rules', 'suggestedPoinMin', 'suggestedDisplayOrder'));
    }

    /**
     * Store new pembinaan internal rule.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'poin_min' => 'required|integer|min:0',
            'poin_max' => 'nullable|integer|gte:poin_min',
            'pembina_roles' => 'required|array|min:1',
            'pembina_roles.*' => 'string',
            'keterangan' => 'required|string|max:500',
            'display_order' => 'nullable|integer|min:1',
        ]);
        
        // Check range overlap
        $this->validateNoOverlap($validated['poin_min'], $validated['poin_max']);
        
        // Auto-assign display_order if not provided
        if (!isset($validated['display_order'])) {
            $maxOrder = PembinaanInternalRule::max('display_order');
            $validated['display_order'] = ($maxOrder ?? 0) + 1;
        }
        
        PembinaanInternalRule::create($validated);
        
        return redirect()
            ->route('pembinaan-internal-rules.index')
            ->with('success', 'Aturan pembinaan internal berhasil ditambahkan');
    }

    /**
     * Update existing pembinaan internal rule.
     */
    public function update(Request $request, $id)
    {
        $rule = PembinaanInternalRule::findOrFail($id);
        
        $validated = $request->validate([
            'poin_min' => 'required|integer|min:0',
            'poin_max' => 'nullable|integer|gte:poin_min',
            'pembina_roles' => 'required|array|min:1',
            'pembina_roles.*' => 'string',
            'keterangan' => 'required|string|max:500',
            'display_order' => 'nullable|integer|min:1',
        ]);
        
        // Check range overlap (exclude current rule)
        $this->validateNoOverlap($validated['poin_min'], $validated['poin_max'], $id);
        
        $rule->update($validated);
        
        return redirect()
            ->route('pembinaan-internal-rules.index')
            ->with('success', 'Aturan pembinaan internal berhasil diupdate');
    }

    /**
     * Delete pembinaan internal rule.
     */
    public function destroy($id)
    {
        $rule = PembinaanInternalRule::findOrFail($id);
        $rule->delete();
        
        return redirect()
            ->route('pembinaan-internal-rules.index')
            ->with('success', 'Aturan pembinaan internal berhasil dihapus');
    }

    /**
     * Validate that new/updated range doesn't overlap with existing rules.
     */
    protected function validateNoOverlap($poinMin, $poinMax, $excludeRuleId = null)
    {
        $query = PembinaanInternalRule::query();
        
        if ($excludeRuleId) {
            $query->where('id', '!=', $excludeRuleId);
        }
        
        $existingRules = $query->get();
        
        foreach ($existingRules as $existing) {
            $existingMin = $existing->poin_min;
            $existingMax = $existing->poin_max ?? PHP_INT_MAX;
            $newMax = $poinMax ?? PHP_INT_MAX;
            
            // Check if ranges overlap
            if ($poinMin <= $existingMax && $newMax >= $existingMin) {
                $rangeText = $existing->poin_max 
                    ? "{$existingMin}-{$existing->poin_max}" 
                    : "{$existingMin}+";
                    
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'poin_min' => "Range overlap dengan aturan existing (range: {$rangeText} poin)"
                ]);
            }
        }
    }
}


