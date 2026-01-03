<?php

namespace App\Http\Requests\TindakLanjut;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\StatusTindakLanjut;

/**
 * Create Tindak Lanjut Form Request
 * 
 * Validasi untuk create tindak lanjut baru.
 */
class CreateTindakLanjutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Operator Sekolah dan Kepala Sekolah yang boleh create tindak lanjut manual.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Operator Sekolah', 'Kepala Sekolah']) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $statusValues = array_map(fn($status) => $status->value, StatusTindakLanjut::cases());

        return [
            'siswa_id' => ['required', 'exists:siswa,id'],
            'pemicu' => ['required', 'string', 'max:500'],
            'sanksi_deskripsi' => ['required', 'string', 'max:500'],
            'denda_deskripsi' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', 'in:' . implode(',', $statusValues)],
            'tanggal_tindak_lanjut' => ['required', 'date'],
            'penyetuju_user_id' => ['nullable', 'exists:users,id'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'siswa_id.required' => 'Siswa wajib dipilih.',
            'siswa_id.exists' => 'Siswa yang dipilih tidak valid.',
            'pemicu.required' => 'Pemicu wajib diisi.',
            'sanksi_deskripsi.required' => 'Sanksi wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'tanggal_tindak_lanjut.required' => 'Tanggal tindak lanjut wajib diisi.',
            'tanggal_tindak_lanjut.date' => 'Format tanggal tidak valid.',
            'penyetuju_user_id.exists' => 'Penyetuju yang dipilih tidak valid.',
        ];
    }
}
