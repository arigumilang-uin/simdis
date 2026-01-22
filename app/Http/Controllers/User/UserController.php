<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use App\Data\User\UserData;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * User Controller - Clean Architecture Pattern
 * 
 * PERAN: Kurir (Courier)
 * - Menerima HTTP Request
 * - Validasi (via FormRequest)
 * - Convert ke DTO
 * - Panggil Service
 * - Return Response
 * 
 * ATURAN:
 * - TIDAK BOLEH ada business logic
 * - TIDAK BOLEH ada query database langsung (use models in constructor if needed)
 * - TIDAK BOLEH ada manipulasi data
 * - Target: < 20 baris per method
 */
class UserController extends Controller
{
    /**
     * Inject UserService via constructor.
     *
     * @param UserService $userService
     */
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Display list of users with filters.
     */
    public function index(Request $request): View
    {
        $filters = [
            'role_id' => $request->input('role_id'),
            'is_active' => $request->input('is_active'),
            'search' => $request->input('search'),
        ];

        $users = $this->userService->getPaginatedUsers(20, $filters);
        
        // Return partial view if requested
        if ($request->ajax() || $request->has('render_partial')) {
            return view('users._table', compact('users'));
        }

        $roles = $this->userService->getAllRoles();

        return view('users.index', compact('users', 'roles', 'filters'));
    }

    /**
     * Show create user form.
     * 
     * CLEAN: Fetch master data via service
     */
    public function create(): View
    {
        $roles = $this->userService->getAllRoles();
        $kelas = $this->userService->getAllKelas();
        $jurusan = $this->userService->getAllJurusan();
        $siswa = $this->userService->getAllSiswa();
        
        return view('users.create', compact('roles', 'kelas', 'jurusan', 'siswa'));
    }

    /**
     * Store new user.
     */
    public function store(CreateUserRequest $request): RedirectResponse
    {
        // Get validated data with additional fields
        $validated = $request->validated();
        
        // Pass all data including optional kelas_id, jurusan_id, siswa_ids to service via DTO
        $dto = UserData::from($request->validated());
        $this->userService->createUser($dto);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Show user detail.
     */
    public function show(int $id): View
    {
        $user = $this->userService->getUser($id);
        return view('users.show', compact('user'));
    }

    /**
     * Show edit user form.
     * 
     * CLEAN: Fetch master data via service
     */
    public function edit(int $id): View
    {
        $user = $this->userService->getUser($id);
        $roles = $this->userService->getAllRoles();
        $kelas = $this->userService->getAllKelas();
        $jurusan = $this->userService->getAllJurusan();
        $siswa = $this->userService->getAllSiswa();
        $connectedSiswaIds = $this->userService->getConnectedSiswaIds($id);
        
        return view('users.edit', compact('user', 'roles', 'kelas', 'jurusan', 'siswa', 'connectedSiswaIds'));
    }

    /**
     * Update user.
     */
    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        $userData = UserData::from($request->validated());
        
        $this->userService->updateUser($id, $userData);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Soft delete user (archive).
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->userService->deleteUser($id);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diarsipkan.');
    }

    /**
     * Display archived users.
     */
    public function trash(Request $request): View
    {
        $query = \App\Models\User::onlyTrashed()
            ->with('role')
            ->orderBy('deleted_at', 'desc');

        // Filter: Search (Username or Name)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        // Filter: Role
        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }

        // Filter: Date Range (deleted_at)
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('deleted_at', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('deleted_at', '<=', $endDate);
        }

        $users = $query->get();
        $roles = $this->userService->getAllRoles();

        return view('users.trash', compact('users', 'roles'));
    }

    /**
     * Restore soft deleted user.
     */
    public function restore(int $id): RedirectResponse
    {
        $user = \App\Models\User::onlyTrashed()->findOrFail($id);
        
        // Check if email/username (without suffix) already exists
        $cleanEmail = preg_replace('/_deleted_\d+$/', '', $user->email);
        $cleanUsername = preg_replace('/_deleted_\d+$/', '', $user->username);
        
        if (\App\Models\User::where('email', $cleanEmail)->exists()) {
            return redirect()
                ->route('users.trash')
                ->with('error', "Email '{$cleanEmail}' sudah digunakan oleh user lain. Tidak dapat memulihkan.");
        }
        
        if (\App\Models\User::where('username', $cleanUsername)->exists()) {
            return redirect()
                ->route('users.trash')
                ->with('error', "Username '{$cleanUsername}' sudah digunakan oleh user lain. Tidak dapat memulihkan.");
        }
        
        $user->restore();

        return redirect()
            ->route('users.trash')
            ->with('success', "User '{$user->nama}' berhasil dipulihkan.");
    }

    /**
     * Permanently delete user.
     */
    public function forceDelete(int $id): RedirectResponse
    {
        $user = \App\Models\User::onlyTrashed()->findOrFail($id);
        
        // Check if user has related records that prevent deletion
        $hasJadwal = \App\Models\JadwalMengajar::withTrashed()
            ->where('user_id', $user->id)
            ->exists();
            
        $hasRiwayat = \App\Models\RiwayatPelanggaran::withTrashed()
            ->where('guru_pencatat_user_id', $user->id)
            ->exists();
            
        if ($hasJadwal || $hasRiwayat) {
            return redirect()
                ->route('users.trash')
                ->with('error', 'Tidak dapat menghapus permanen user yang memiliki data jadwal mengajar atau riwayat pencatatan pelanggaran.');
        }
        
        $nama = $user->nama;
        $user->forceDelete();

        return redirect()
            ->route('users.trash')
            ->with('success', "User '{$nama}' berhasil dihapus secara permanen.");
    }

    /**
     * Show reset password form.
     */
    public function resetPasswordForm(int $id): View
    {
        $user = $this->userService->getUser($id);
        return view('users.reset-password', compact('user'));
    }

    /**
     * Reset user password (by admin).
     */
    public function resetPassword(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->userService->resetPassword($id, $request->password);

        return redirect()
            ->route('users.index')
            ->with('success', 'Password berhasil direset.');
    }

    /**
     * Toggle user activation.
     */
    public function toggleActivation(int $id): RedirectResponse
    {
        $this->userService->toggleActivation($id);

        return redirect()
            ->back()
            ->with('success', 'Status aktivasi user berhasil diubah.');
    }

    /**
     * Show own profile.
     */
    public function showProfile(): View
    {
        $user = $this->userService->getUser(auth()->id());
        return view('users.profile', compact('user'));  // Fixed: use existing profile.blade.php
    }

    /**
     * Show edit own profile form.
     * 
     * NOTE: Using simple profile view (create if not exists)
     */
    public function editProfile(): View
    {
        $user = $this->userService->getUser(auth()->id());
        return view('users.profile', compact('user'));  // Simpler profile edit view
    }

    /**
     * Update own profile.
     * 
     * LOGIC (Updated 2026-01-07):
     * - nama: AUTO-GENERATED (cannot be edited by user)
     * - username (Nama Lengkap): 
     *   - Wali Murid: CAN EDIT
     *   - Other roles: CANNOT EDIT (only Operator Sekolah can)
     * - email, phone, nip, ni_pppk, nuptk: EDITABLE
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $userId = auth()->id();
        $user = auth()->user();
        $isWaliMurid = $user->hasRole('Wali Murid');
        
        // Build validation rules
        $rules = [
            'email' => [
                'nullable',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'nip' => ['nullable', 'string', 'max:20', \Illuminate\Validation\Rule::unique('users', 'nip')->ignore($userId)],
            'ni_pppk' => ['nullable', 'string', 'max:50', \Illuminate\Validation\Rule::unique('users', 'ni_pppk')->ignore($userId)],
            'nuptk' => ['nullable', 'string', 'max:20', \Illuminate\Validation\Rule::unique('users', 'nuptk')->ignore($userId)],
        ];
        
        // Only validate username for Wali Murid
        if ($isWaliMurid) {
            $rules['username'] = [
                'required',
                'string',
                'max:100',
                \Illuminate\Validation\Rule::unique('users', 'username')->ignore($userId),
            ];
        }
        
        $request->validate($rules);

        // Build update data
        $updateData = [
            'id' => $userId,
            'nama' => $user->nama, // KEEP EXISTING (auto-generated)
            'email' => $request->email,
            'phone' => $request->phone,
            'nip' => $request->nip,
            'ni_pppk' => $request->ni_pppk,
            'nuptk' => $request->nuptk,
            'role_id' => $user->role_id,
            'is_active' => $user->is_active,
        ];
        
        // Only allow username change for Wali Murid
        if ($isWaliMurid && $request->has('username')) {
            $updateData['username'] = $request->username;
        } else {
            $updateData['username'] = $user->username; // Keep existing
        }

        $userData = UserData::from($updateData);
        $this->userService->updateUser($userId, $userData);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Profile berhasil diperbarui.');
    }

    /**
     * Show change password form.
     */
    public function changePasswordForm(): View
    {
        return view('users.change-password');
    }

    /**
     * Change own password.
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'old_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $this->userService->changePassword(
                auth()->id(),
                $request->old_password,
                $request->password
            );

            return redirect()
                ->route('profile.show')
                ->with('success', 'Password berhasil diubah.');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Export users (placeholder).
     */
    public function export()
    {
        // TODO: Implement export logic
        return response()->download('path/to/export.xlsx');
    }

    /**
     * Bulk activate users.
     */
    public function bulkActivate(Request $request): RedirectResponse
    {
        $request->validate(['ids' => 'required|string']);
        $ids = explode(',', $request->input('ids'));
        
        $this->userService->bulkActivate($ids);

        return redirect()
            ->back()
            ->with('success', count($ids) . ' user berhasil diaktifkan.');
    }

    /**
     * Bulk deactivate users.
     */
    public function bulkDeactivate(Request $request): RedirectResponse
    {
        $request->validate(['ids' => 'required|string']);
        $ids = explode(',', $request->input('ids'));

        $this->userService->bulkDeactivate($ids);

        return redirect()
            ->back()
            ->with('success', count($ids) . ' user berhasil dinonaktifkan.');
    }

    /**
     * Bulk delete users.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate(['ids' => 'required|string']);
        $ids = explode(',', $request->input('ids'));

        $this->userService->bulkDelete($ids);

        return redirect()
            ->back()
            ->with('success', count($ids) . ' user berhasil dihapus.');
    }

    /**
     * Bulk restore archived users.
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $request->validate(['ids' => 'required|string']);
        $ids = explode(',', $request->input('ids'));

        $users = \App\Models\User::onlyTrashed()->whereIn('id', $ids)->get();
        $restoredCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($users as $user) {
            // Check if email/username (without suffix) already exists
            $cleanEmail = preg_replace('/_deleted_\d+$/', '', $user->email);
            $cleanUsername = preg_replace('/_deleted_\d+$/', '', $user->username);
            
            if (\App\Models\User::where('email', $cleanEmail)->exists()) {
                $skippedCount++;
                $errors[] = "Email '{$cleanEmail}' sudah digunakan.";
                continue;
            }
            
            if (\App\Models\User::where('username', $cleanUsername)->exists()) {
                $skippedCount++;
                $errors[] = "Username '{$cleanUsername}' sudah digunakan.";
                continue;
            }
            
            $user->restore();
            $restoredCount++;
        }

        $message = "{$restoredCount} user berhasil dipulihkan.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} user dilewati karena konflik email/username.";
        }

        return redirect()
            ->route('users.trash')
            ->with($restoredCount > 0 ? 'success' : 'error', $message);
    }

    /**
     * Bulk force delete archived users.
     */
    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $request->validate(['ids' => 'required|string']);
        $ids = explode(',', $request->input('ids'));

        $users = \App\Models\User::onlyTrashed()->whereIn('id', $ids)->get();
        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($users as $user) {
            // Check if user has related records that prevent deletion
            $hasJadwal = \App\Models\JadwalMengajar::withTrashed()
                ->where('user_id', $user->id)
                ->exists();
                
            $hasRiwayat = \App\Models\RiwayatPelanggaran::withTrashed()
                ->where('guru_pencatat_user_id', $user->id)
                ->exists();
                
            if ($hasJadwal || $hasRiwayat) {
                $skippedCount++;
                continue;
            }
            
            $user->forceDelete();
            $deletedCount++;
        }

        $message = "{$deletedCount} user berhasil dihapus permanen.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} user dilewati karena memiliki data terkait.";
        }

        return redirect()
            ->route('users.trash')
            ->with($deletedCount > 0 ? 'success' : 'error', $message);
    }

    /**
     * Import users.
     */
    public function import(Request $request): RedirectResponse
    {
        // TODO: Implement import logic
        return redirect()
            ->back()
            ->with('success', 'Users berhasil diimport.');
    }
}
