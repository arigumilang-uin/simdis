<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Create Jurusan Request
 * 
 * Purpose: Validation for creating new jurusan
 * Pattern: FormRequest (Laravel)
 * Responsibility: Validation ONLY
 */
class CreateJurusanRequest extends FormRequest
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
     * EXACT rules from original controller (lines 34-38)
     */
    public function rules(): array
    {
        return [
            'nama_jurusan' => ['required', 'string', 'max:191'],
            'kode_jurusan' => ['nullable', 'string', 'max:20', 'unique:jurusan,kode_jurusan'],
            'kaprodi_user_id' => ['nullable', 'exists:users,id'],
            'create_kaprodi' => ['nullable', 'boolean'], // For auto-creating kaprodi user
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'nama_jurusan' => 'Nama Jurusan',
            'kode_jurusan' => 'Kode Jurusan',
            'kaprodi_user_id' => 'Kaprodi',
        ];
    }
}
