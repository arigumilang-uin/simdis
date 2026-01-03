<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Create Siswa Form Request
 * 
 * Validasi untuk create siswa baru.
 * Aturan validasi dipindahkan dari controller lama (baris 122-130).
 */
class CreateSiswaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Hanya Operator Sekolah yang boleh create siswa.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Operator Sekolah') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * Aturan validasi sama persis dengan controller lama untuk menjaga kompatibilitas.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nisn' => ['required', 'numeric', 'unique:siswa,nisn'],
            'nama_siswa' => ['required', 'string', 'max:255'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'nomor_hp_wali_murid' => ['nullable', 'numeric'],
            'wali_murid_user_id' => ['nullable', 'exists:users,id'],
            'create_wali' => ['nullable', 'boolean'],
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
            'nisn.unique' => 'NISN sudah terdaftar dalam sistem.',
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
            'create_wali' => 'buat akun wali',
        ];
    }
}
