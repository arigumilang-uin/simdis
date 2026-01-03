<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update JenisPelanggaran Request
 * 
 * Purpose: Validation for updating existing jenis pelanggaran
 * Pattern: FormRequest (Laravel)
 * Responsibility: Validation ONLY
 */
class UpdateJenisPelanggaranRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization handled by middleware
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * EXACT rules from original controller (lines 87-93)
     */
    public function rules(): array
    {
        return [
            'nama_pelanggaran' => ['required', 'string', 'max:255'],
            'kategori_id' => ['required', 'exists:kategori_pelanggaran,id'],
            'poin' => ['nullable', 'integer', 'min:0'],
            'filter_category' => ['nullable', 'in:atribut,absensi,kerapian,ibadah,berat'],
            'keywords' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'nama_pelanggaran' => 'Nama Pelanggaran',
            'kategori_id' => 'Kategori',
            'filter_category' => 'Filter Kategori',
            'keywords' => 'Keywords',
        ];
    }
}
