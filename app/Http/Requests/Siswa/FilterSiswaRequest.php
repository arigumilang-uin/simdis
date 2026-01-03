<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Filter Siswa Form Request
 * 
 * Validasi untuk filter/search siswa di halaman index.
 * Input filter dari user akan divalidasi sebelum dikonversi ke SiswaFilterData.
 */
class FilterSiswaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Semua authenticated user bisa filter siswa (sesuai role scope mereka).
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * Validasi input filter untuk memastikan data yang masuk valid.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cari' => ['nullable', 'string', 'max:255'], // Search keyword
            'search' => ['nullable', 'string', 'max:255'], // Alias untuk cari
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
            'jurusan_id' => ['nullable', 'integer', 'exists:jurusan,id'],
            'wali_murid_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'tingkat' => ['nullable', 'string', 'in:X,XI,XII'],
            'with_violations' => ['nullable', 'boolean'],
            'with_active_cases' => ['nullable', 'boolean'],
            'perPage' => ['nullable', 'integer', 'min:5', 'max:100'],
            'sortBy' => ['nullable', 'string', 'in:nama_siswa,nisn,kelas_id'],
            'sortDirection' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    /**
     * Get validated data with default values.
     * 
     * Helper method untuk mendapatkan filter data dengan default values.
     *
     * @return array
     */
    public function getFilterData(): array
    {
        $validated = $this->validated();

        return [
            'search' => $validated['search'] ?? $validated['cari'] ?? null,
            'kelas_id' => $validated['kelas_id'] ?? null,
            'jurusan_id' => $validated['jurusan_id'] ?? null,
            'wali_murid_user_id' => $validated['wali_murid_user_id'] ?? null,
            'with_violations' => $validated['with_violations'] ?? false,
            'with_active_cases' => $validated['with_active_cases'] ?? false,
            'perPage' => $validated['perPage'] ?? 20,
            'sortBy' => $validated['sortBy'] ?? 'nama_siswa',
            'sortDirection' => $validated['sortDirection'] ?? 'asc',
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
            'kelas_id.exists' => 'Kelas yang dipilih tidak valid.',
            'jurusan_id.exists' => 'Jurusan yang dipilih tidak valid.',
            'wali_murid_user_id.exists' => 'Wali murid yang dipilih tidak valid.',
            'tingkat.in' => 'Tingkat harus salah satu dari: X, XI, XII.',
            'perPage.min' => 'Jumlah data per halaman minimal 5.',
            'perPage.max' => 'Jumlah data per halaman maksimal 100.',
            'sortBy.in' => 'Kolom sort tidak valid.',
            'sortDirection.in' => 'Arah sort harus asc atau desc.',
        ];
    }
}
