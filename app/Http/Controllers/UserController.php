<?php

namespace App\Http\Controllers;

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
        
        // Pass all data including optional kelas_id, jurusan_id, siswa_ids to service
        $this->userService->createUser($validated);

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

        // Handle role-specific assignments
        // Kelas assignment for Wali Kelas/Developer
        if ($request->filled('kelas_id')) {
            $this->userService->assignKelas($id, $request->input('kelas_id'));
        }
        
        // Jurusan assignment for Kaprodi/Developer
        if ($request->filled('jurusan_id')) {
            $this->userService->assignJurusan($id, $request->input('jurusan_id'));
        }

        // Siswa linking for Wali Murid/Developer
        if ($request->has('siswa_ids')) {
            $this->userService->linkSiswa($id, $request->input('siswa_ids', []));
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Delete user.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->userService->deleteUser($id);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dihapus.');
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
     * NEW LOGIC (Updated 2025-12-11):
     * - nama: AUTO-GENERATED (cannot be edited by user)
     * - username: EDITABLE (user's login identifier)
     * - email, phone: EDITABLE
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $userId = auth()->id();
        
        $request->validate([
            'username' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('users', 'username')->ignore($userId),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $userData = UserData::from([
            'id' => $userId,
            'nama' => auth()->user()->nama, // KEEP EXISTING (auto-generated)
            'username' => $request->username, // ALLOW EDIT
            'email' => $request->email,
            'phone' => $request->phone,
            'role_id' => auth()->user()->role_id,
            'is_active' => auth()->user()->is_active,
        ]);

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
