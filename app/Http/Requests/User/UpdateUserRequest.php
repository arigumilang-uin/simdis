<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Update User Request
 * 
 * Validation for updating existing user.
 * Authorization: Only Operator Sekolah and Kepala Sekolah.
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['Operator Sekolah', 'Kepala Sekolah']);
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * UPDATED 2025-12-11 (CORRECTED):
     * - nama: EDITABLE by operator (for manual override of auto-sync)
     * - username: EDITABLE (user's login identifier)
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user'); // From route parameter

        return [
            'role_id' => ['sometimes', 'exists:roles,id'],
            // Nama = Auto-generated berdasarkan role (tidak perlu input dari operator)
            'nama' => ['nullable', 'string', 'max:255'],
            // Username = Nama asli user (bisa dengan gelar, spasi, dll)
            'username' => [
                'required',
                'string',
                'max:100',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'nip' => [
                'nullable', 
                'string', 
                'max:20',
                Rule::unique('users', 'nip')->ignore($userId),
            ],
            'ni_pppk' => [
                'nullable', 
                'string', 
                'max:50',
                Rule::unique('users', 'ni_pppk')->ignore($userId),
            ],
            'nuptk' => [
                'nullable', 
                'string', 
                'max:20',
                Rule::unique('users', 'nuptk')->ignore($userId),
            ],
            'is_active' => ['boolean'],
            
            // Role-specific assignments
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'jurusan_id' => ['nullable', 'exists:jurusan,id'],
            
            // Siswa linking for Wali Murid/Developer roles
            'siswa_ids' => ['nullable', 'array'],
            'siswa_ids.*' => ['exists:siswa,id'],
            
            // BUGFIX: Allow password change
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'role_id' => 'Role',
            'nama' => 'Nama Lengkap',
            'username' => 'Username',
            'email' => 'Email',
            'phone' => 'Nomor HP',
            'nip' => 'NIP',
            'ni_pppk' => 'NI PPPK',
            'nuptk' => 'NUPTK',
            'is_active' => 'Status Aktif',
            'password' => 'Password Baru',
            'password_confirmation' => 'Konfirmasi Password',
        ];
    }
}
