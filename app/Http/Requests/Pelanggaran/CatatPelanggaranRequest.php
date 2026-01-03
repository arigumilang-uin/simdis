<?php

namespace App\Http\Requests\Pelanggaran;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Catat Pelanggaran Form Request
 * 
 * Validasi untuk catat pelanggaran baru.
 * Validasi di-extract dari controller lama (RiwayatController baris 229-235).
 */
class CatatPelanggaranRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Teacher roles (Guru, Wali Kelas, Kaprodi, dll) dapat mencatat pelanggaran.
     */
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * Rules yang sama persis dengan controller lama untuk backward compatibility.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'siswa_id' => ['required', 'array', 'min:1'],
            'siswa_id.*' => ['required', 'exists:siswa,id'],
            'jenis_pelanggaran_id' => ['required', 'array', 'min:1'],
            'jenis_pelanggaran_id.*' => ['required', 'exists:jenis_pelanggaran,id'],
            'tanggal_kejadian' => ['required', 'date'],
            'jam_kejadian' => ['nullable', 'date_format:H:i'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'bukti_foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
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
            'jenis_pelanggaran_id.required' => 'Jenis pelanggaran wajib dipilih.',
            'jenis_pelanggaran_id.exists' => 'Jenis pelanggaran yang dipilih tidak valid.',
            'tanggal_kejadian.required' => 'Tanggal kejadian wajib diisi.',
            'tanggal_kejadian.date' => 'Format tanggal tidak valid.',
            'jam_kejadian.date_format' => 'Format jam harus HH:MM (contoh: 08:30).',
            'keterangan.max' => 'Keterangan maksimal 1000 karakter.',
            'bukti_foto.image' => 'File harus berupa gambar.',
            'bukti_foto.mimes' => 'Format gambar harus jpeg, png, atau jpg.',
            'bukti_foto.max' => 'Ukuran gambar maksimal 2MB.',
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
            'siswa_id' => 'siswa',
            'jenis_pelanggaran_id' => 'jenis pelanggaran',
            'tanggal_kejadian' => 'tanggal kejadian',
            'jam_kejadian' => 'jam kejadian',
            'keterangan' => 'keterangan',
            'bukti_foto' => 'bukti foto',
        ];
    }

    /**
     * Prepare data for validation (merge tanggal + jam).
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Auto-set guru_pencatat_user_id ke user yang login
        $this->merge([
            'guru_pencatat_user_id' => $this->user()?->id,
        ]);
    }

    /**
     * Get the combined datetime from tanggal_kejadian and jam_kejadian.
     *
     * @return string
     */
    public function getCombinedDateTime(): string
    {
        $time = $this->input('jam_kejadian') ?? now()->format('H:i');
        
        try {
            return \Carbon\Carbon::createFromFormat('Y-m-d H:i', $this->tanggal_kejadian . ' ' . $time)
                ->toDateTimeString();
        } catch (\Exception $e) {
            return \Carbon\Carbon::parse($this->tanggal_kejadian . ' ' . $time)
                ->toDateTimeString();
        }
    }
}
