<?php

namespace App\Http\Requests\Pelanggaran;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Filter Riwayat Pelanggaran Form Request
 * 
 * Validasi untuk filter/search riwayat pelanggaran di halaman index.
 * Input filter dari user akan divalidasi sebelum dikonversi ke RiwayatPelanggaranFilterData.
 */
class FilterRiwayatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Semua authenticated user bisa filter riwayat (sesuai role scope mereka).
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
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'tanggal_dari' => ['nullable', 'date'],
            'tanggal_sampai' => ['nullable', 'date', 'after_or_equal:tanggal_dari'],
            'jenis_pelanggaran_id' => ['nullable', 'integer', 'exists:jenis_pelanggaran,id'],
            'pencatat_id' => ['nullable', 'integer', 'exists:users,id'],
            'guru_pencatat_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'siswa_id' => ['nullable', 'integer', 'exists:siswa,id'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
            'jurusan_id' => ['nullable', 'integer', 'exists:jurusan,id'],
            'cari_siswa' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
            'tingkat' => ['nullable', 'string', 'in:RINGAN,SEDANG,BERAT'],
            'perPage' => ['nullable', 'integer', 'min:5', 'max:100'],
            'sortBy' => ['nullable', 'string', 'in:tanggal_kejadian,siswa_id,jenis_pelanggaran_id'],
            'sortDirection' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    /**
     * Get validated data with default values.
     * 
     * Helper method untuk mendapatkan filter data dengan default values
     * dan normalisasi field names (support backward compatibility).
     *
     * @return array
     */
    public function getFilterData(): array
    {
        $validated = $this->validated();

        return [
            'siswa_id' => $validated['siswa_id'] ?? null,
            'jenis_pelanggaran_id' => $validated['jenis_pelanggaran_id'] ?? null,
            'guru_pencatat_user_id' => $validated['guru_pencatat_user_id'] ?? $validated['pencatat_id'] ?? null,
            'kelas_id' => $validated['kelas_id'] ?? null,
            'jurusan_id' => $validated['jurusan_id'] ?? null,
            'tingkat' => !empty($validated['tingkat']) ? \App\Enums\TingkatPelanggaran::from($validated['tingkat']) : null,
            'tanggal_dari' => $validated['tanggal_dari'] ?? $validated['start_date'] ?? null,
            'tanggal_sampai' => $validated['tanggal_sampai'] ?? $validated['end_date'] ?? null,
            'search' => $validated['search'] ?? $validated['cari_siswa'] ?? null,
            'perPage' => $validated['perPage'] ?? 20,
            'sortBy' => $validated['sortBy'] ?? 'tanggal_kejadian',
            'sortDirection' => $validated['sortDirection'] ?? 'desc',
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
            'end_date.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal.',
            'tanggal_sampai.after_or_equal' => 'Tanggal sampai harus sama atau setelah tanggal dari.',
            'jenis_pelanggaran_id.exists' => 'Jenis pelanggaran yang dipilih tidak valid.',
            'pencatat_id.exists' => 'Guru pencatat yang dipilih tidak valid.',
            'guru_pencatat_user_id.exists' => 'Guru pencatat yang dipilih tidak valid.',
            'siswa_id.exists' => 'Siswa yang dipilih tidak valid.',
            'kelas_id.exists' => 'Kelas yang dipilih tidak valid.',
            'jurusan_id.exists' => 'Jurusan yang dipilih tidak valid.',
            'tingkat.in' => 'Tingkat harus salah satu dari: RINGAN, SEDANG, BERAT.',
            'perPage.min' => 'Jumlah data per halaman minimal 5.',
            'perPage.max' => 'Jumlah data per halaman maksimal 100.',
            'sortBy.in' => 'Kolom sort tidak valid.',
            'sortDirection.in' => 'Arah sort harus asc atau desc.',
        ];
    }
}
