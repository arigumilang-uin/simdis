<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Kelas Request
 * 
 * Purpose: Validation for updating existing kelas
 * Pattern: FormRequest (Laravel)
 * Responsibility: Validation ONLY
 */
class UpdateKelasRequest extends FormRequest
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
     * EXACT rules from original controller (lines 148-153)
     */
    public function rules(): array
    {
        return [
            'nama_kelas' => ['required', 'string', 'max:100'],
            'tingkat' => ['required', 'string', 'in:X,XI,XII'],
            'jurusan_id' => ['required', 'integer', 'exists:jurusan,id'],
            'konsentrasi_id' => ['nullable', 'integer', 'exists:konsentrasi,id'],
            'wali_kelas_user_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'nama_kelas' => 'Nama Kelas',
            'tingkat' => 'Tingkat',
            'jurusan_id' => 'Jurusan',
            'wali_kelas_user_id' => 'Wali Kelas',
        ];
    }
}
