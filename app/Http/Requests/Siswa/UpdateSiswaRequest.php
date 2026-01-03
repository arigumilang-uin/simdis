<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Siswa Form Request
 * 
 * Validasi untuk update siswa.
 * Aturan validasi dipindahkan dari controller lama (baris 241-257).
 * 
 * LOGIKA ROLE-BASED:
 * - Wali Kelas: hanya boleh update nomor_hp_wali_murid
 * - Operator: boleh update semua field
 */
class UpdateSiswaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Operator Sekolah dan Wali Kelas boleh update siswa.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Operator Sekolah', 'Wali Kelas']) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * Rules berbeda tergantung role user (dari controller lama baris 235-268).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $siswaId = $this->route('siswa'); // Ambil ID dari route parameter

        // Jika Wali Kelas: hanya validasi nomor HP
        if ($user?->hasRole('Wali Kelas')) {
            return [
                'nomor_hp_wali_murid' => ['nullable', 'numeric'],
            ];
        }

        // Jika Operator: validasi semua field
        return [
            'nisn' => ['required', 'numeric', 'unique:siswa,nisn,' . $siswaId],
            'nama_siswa' => ['required', 'string', 'max:255'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'nomor_hp_wali_murid' => ['nullable', 'numeric'],
            'wali_murid_user_id' => ['nullable', 'exists:users,id'],
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
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.numeric' => 'NISN harus berupa angka.',
            'nisn.unique' => 'NISN sudah terdaftar pada siswa lain.',
            'nama_siswa.required' => 'Nama siswa wajib diisi.',
            'nama_siswa.max' => 'Nama siswa maksimal 255 karakter.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'kelas_id.exists' => 'Kelas yang dipilih tidak valid.',
            'nomor_hp_wali_murid.numeric' => 'Nomor HP harus berupa angka.',
            'wali_murid_user_id.exists' => 'Wali murid yang dipilih tidak valid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nisn' => 'NISN',
            'nama_siswa' => 'nama siswa',
            'kelas_id' => 'kelas',
            'nomor_hp_wali_murid' => 'nomor HP wali murid',
            'wali_murid_user_id' => 'wali murid',
        ];
    }

    /**
     * Check if user is Wali Kelas (untuk digunakan di service).
     *
     * @return bool
     */
    public function isWaliKelas(): bool
    {
        return $this->user()?->hasRole('Wali Kelas') ?? false;
    }
}
