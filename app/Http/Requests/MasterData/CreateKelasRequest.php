<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Create Kelas Request
 * 
 * Purpose: Validation for creating new kelas
 * Pattern: FormRequest (Laravel)
 * Responsibility: Validation ONLY
 */
class CreateKelasRequest extends FormRequest
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
     * EXACT rules from original controller (lines 48-52)
     */
    public function rules(): array
    {
        return [
            'tingkat' => ['required', 'string', 'in:X,XI,XII'],
            'jurusan_id' => ['required', 'integer', 'exists:jurusan,id'],
            'konsentrasi_id' => ['nullable', 'integer', 'exists:konsentrasi,id'],
            'wali_kelas_user_id' => ['nullable', 'integer'],
            'create_wali' => ['nullable', 'boolean'],
            'rombel' => ['nullable', 'string', 'max:10'], // For auto-creating wali kelas user
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'tingkat' => 'Tingkat',
            'jurusan_id' => 'Jurusan',
            'wali_kelas_user_id' => 'Wali Kelas',
        ];
    }
}
