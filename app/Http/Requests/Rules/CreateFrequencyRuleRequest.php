<?php

namespace App\Http\Requests\Rules;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Create Frequency Rule Request
 * 
 * Validation for creating frequency rules
 */
class CreateFrequencyRuleRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'frequency_min' => ['required', 'integer', 'min:1'],
            'frequency_max' => ['nullable', 'integer', 'min:1', 'gte:frequency_min'],
            'poin' => ['required', 'integer', 'min:0'],
            'sanksi_description' => ['required', 'string', 'max:500'],
            'trigger_surat' => ['nullable', 'boolean'],
            'pembina_roles' => ['required', 'array', 'min:1'],
            'pembina_roles.*' => ['string', 'in:Wali Kelas,Kaprodi,Waka Kesiswaan,Waka Sarana,Kepala Sekolah,Semua Guru & Staff'],
            'display_order' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'frequency_min' => 'frekuensi minimum',
            'frequency_max' => 'frekuensi maksimum',
            'poin' => 'poin pelanggaran',
            'sanksi_description' => 'deskripsi sanksi',
            'trigger_surat' => 'trigger surat',
            'pembina_roles' => 'pembina yang terlibat',
            'display_order' => 'urutan tampilan',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'pembina_roles.required' => 'Minimal harus memilih 1 pembina yang terlibat.',
            'pembina_roles.min' => 'Minimal harus memilih 1 pembina yang terlibat.',
            'frequency_max.gte' => 'Frekuensi maksimum harus lebih besar atau sama dengan frekuensi minimum.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert trigger_surat to boolean
        if ($this->has('trigger_surat')) {
            $this->merge([
                'trigger_surat' => $this->boolean('trigger_surat'),
            ]);
        }
    }
}
