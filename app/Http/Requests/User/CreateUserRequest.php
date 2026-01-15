<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Role;

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
        // Get role name for conditional validation
        $roleId = $this->input('role_id');
        $roleName = '';
        if ($roleId) {
            $role = Role::find($roleId);
            $roleName = $role?->nama_role ?? '';
        }
        
        $rules = [
            'role_id' => ['required', 'exists:roles,id'],
            // Username = Nama asli user (bisa dengan gelar, spasi, dll)
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'nip' => ['nullable', 'string', 'max:20', 'unique:users,nip'],
            'ni_pppk' => ['nullable', 'string', 'max:50', 'unique:users,ni_pppk'],
            'nuptk' => ['nullable', 'string', 'max:20', 'unique:users,nuptk'],
            'password' => ['required', 'string', 'min:6'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'jurusan_id' => ['nullable', 'exists:jurusan,id'],
            'siswa_ids' => ['nullable', 'array'],
            'siswa_ids.*' => ['exists:siswa,id'],
        ];
        
        // Wali Kelas WAJIB pilih kelas
        if ($roleName === 'Wali Kelas') {
            $rules['kelas_id'] = ['required', 'exists:kelas,id'];
        }
        
        // Kaprodi WAJIB pilih jurusan
        if ($roleName === 'Kaprodi') {
            $rules['jurusan_id'] = ['required', 'exists:jurusan,id'];
        }
        
        // Wali Murid WAJIB pilih minimal 1 siswa
        if ($roleName === 'Wali Murid') {
            $rules['siswa_ids'] = ['required', 'array', 'min:1'];
        }
        
        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'role_id' => 'Role',
            'username' => 'Nama Lengkap',
            'email' => 'Email',
            'phone' => 'Nomor HP',
            'nip' => 'NIP',
            'ni_pppk' => 'NI PPPK',
            'nuptk' => 'NUPTK',
            'password' => 'Password',
            'kelas_id' => 'Kelas',
            'jurusan_id' => 'Jurusan',
            'siswa_ids' => 'Siswa',
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'kelas_id.required' => 'Wali Kelas harus diassign ke sebuah kelas.',
            'jurusan_id.required' => 'Kaprodi harus diassign ke sebuah jurusan.',
            'siswa_ids.required' => 'Wali Murid harus diassign ke minimal 1 siswa.',
            'siswa_ids.min' => 'Wali Murid harus diassign ke minimal 1 siswa.',
        ];
    }
}
