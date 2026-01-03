<?php

namespace App\Http\Requests\Pelanggaran;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\RiwayatPelanggaran;
use Carbon\Carbon;

/**
 * Update Pelanggaran Form Request
 * 
 * Validasi untuk update pelanggaran.
 * Includes authorization berdasarkan ownership dan time limit.
 * 
 * AUTHORIZATION RULES (dari controller lama baris 311-331):
 * - Operator Sekolah: Dapat edit semua record tanpa batasan
 * - Role lain: Hanya dapat edit record yang mereka catat sendiri (max 3 hari)
 */
class UpdatePelanggaranRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Complex authorization dengan ownership dan time limit check.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        if (!$user) {
            return false;
        }

        // Get the riwayat record being updated
        $riwayatId = $this->route('riwayat') ?? $this->route('id');
        $riwayat = RiwayatPelanggaran::find($riwayatId);

        if (!$riwayat) {
            return false;
        }

        // Operator Sekolah dapat edit semua record tanpa batasan
        if ($user->hasRole('Operator Sekolah')) {
            return true;
        }

        // Role lain: harus pencatat sendiri
        if ($riwayat->guru_pencatat_user_id !== $user->id) {
            return false;
        }

        // Batasi kemampuan edit sampai 3 hari sejak pencatatan
        if ($riwayat->created_at) {
            $created = Carbon::parse($riwayat->created_at);
            if (Carbon::now()->greaterThan($created->copy()->addDays(3))) {
                return false; // Lebih dari 3 hari
            }
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jenis_pelanggaran_id' => ['required', 'exists:jenis_pelanggaran,id'],
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
            'jenis_pelanggaran_id' => 'jenis pelanggaran',
            'tanggal_kejadian' => 'tanggal kejadian',
            'jam_kejadian' => 'jam kejadian',
            'keterangan' => 'keterangan',
            'bukti_foto' => 'bukti foto',
        ];
    }

    /**
     * Get the error messages for authorization failures.
     *
     * @return array
     */
    public function failedAuthorization()
    {
        $user = $this->user();
        $riwayatId = $this->route('riwayat') ?? $this->route('id');
        $riwayat = RiwayatPelanggaran::find($riwayatId);

        // Determine specific error message
        if ($riwayat && $riwayat->guru_pencatat_user_id !== $user?->id) {
            abort(403, 'AKSES DITOLAK: Anda hanya dapat mengelola riwayat yang Anda catat.');
        }

        if ($riwayat && $riwayat->created_at) {
            $created = Carbon::parse($riwayat->created_at);
            if (Carbon::now()->greaterThan($created->copy()->addDays(3))) {
                abort(403, 'Batas waktu edit telah lewat (lebih dari 3 hari sejak pencatatan).');
            }
        }

        abort(403, 'Anda tidak memiliki akses untuk mengupdate pelanggaran ini.');
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
            return Carbon::createFromFormat('Y-m-d H:i', $this->tanggal_kejadian . ' ' . $time)
                ->toDateTimeString();
        } catch (\Exception $e) {
            return Carbon::parse($this->tanggal_kejadian . ' ' . $time)
                ->toDateTimeString();
        }
    }
}
