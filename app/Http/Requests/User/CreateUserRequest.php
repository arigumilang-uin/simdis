<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Create User Request
 * 
 * Validation for creating new user.
 * Authorization: Only Operator Sekolah and Kepala Sekolah.
 */
class CreateUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role_id' => ['required', 'exists:roles,id'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'nip' => ['nullable', 'string', 'max:20'],
            'nuptk' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'jurusan_id' => ['nullable', 'exists:jurusan,id'],
            'siswa_ids' => ['nullable', 'array'],
            'siswa_ids.*' => ['exists:siswa,id'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'role_id' => 'Role',
            'email' => 'Email',
            'phone' => 'Nomor HP',
            'nip' => 'NIP',
            'nuptk' => 'NUPTK',
            'password' => 'Password',
            'kelas_id' => 'Kelas',
            'jurusan_id' => 'Jurusan',
            'siswa_ids' => 'Siswa',
        ];
    }
}
