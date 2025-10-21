<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserManagement extends Component
{
    use WithPagination;

    // Modal States
    public $showDeleteModal = false;
    public $showEditModal = false;
    public $userToDelete;
    
    // Form Properties
    public $userId;
    public $name;
    public $email;
    public $role;
    public $password;
    public $password_confirmation;

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->userId)],
            'role' => ['required', Rule::in(['admin', 'cashier'])],
            'password' => ['nullable', 'min:8', 'confirmed'],
        ];
    }

    public function mount()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'ANDA TIDAK DIIZINKAN MENGAKSES HALAMAN INI.');
        }
    }

    // Create/Edit Modal Methods
    public function openCreateModal()
    {
        $this->resetForm();
        $this->showEditModal = true;
    }

    public function openEditModal($userId)
    {
        $user = User::findOrFail($userId);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = '';
        $this->password_confirmation = '';
        $this->showEditModal = true;
    }

    public function saveUser()
    {
        // Jika membuat user baru, password wajib diisi
        $rules = $this->rules();
        if (!$this->userId) {
            $rules['password'] = ['required', 'min:8', 'confirmed'];
        }

        $validatedData = $this->validate($rules);

        if ($this->userId) {
            // Update User
            $user = User::findOrFail($this->userId);
            $user->update([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'role' => $validatedData['role'],
            ]);

            if (!empty($validatedData['password'])) {
                $user->update(['password' => Hash::make($validatedData['password'])]);
            }
            $message = 'Pengguna berhasil diperbarui.';
        } else {
            // Create User
            User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'role' => $validatedData['role'],
                'password' => Hash::make($validatedData['password']),
            ]);
            $message = 'Pengguna berhasil dibuat.';
        }

        $this->dispatch('show-alert', ['type' => 'success', 'message' => $message]);
        $this->closeEditModal();
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->resetErrorBag();
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->role = 'cashier';
        $this->password = '';
        $this->password_confirmation = '';
    }

    // Role Change Method
    public function changeRole($userId, $newRole)
    {
        $user = User::findOrFail($userId);

        if (!in_array($newRole, ['admin', 'cashier'])) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Peran tidak valid.']);
            return;
        }

        if ($user->role === 'admin' && User::where('role', 'admin')->count() === 1 && $newRole !== 'admin') {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Tidak dapat mengubah peran satu-satunya admin.']);
            $this->dispatch('$refresh');
            return;
        }

        $user->role = $newRole;
        $user->save();

        $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Peran untuk ' . $user->name . ' berhasil diubah.']);
    }

    // Delete Modal Methods
    public function confirmDelete($userId)
    {
        $this->userToDelete = User::find($userId);
        $this->showDeleteModal = true;
    }

    public function deleteUser()
    {
        if ($this->userToDelete) {
            if ($this->userToDelete->role === 'admin' && User::where('role', 'admin')->count() === 1) {
                $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Tidak dapat menghapus satu-satunya admin.']);
                $this->closeDeleteModal();
                return;
            }

            $userName = $this->userToDelete->name;
            $this->userToDelete->delete();
            $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Pengguna ' . $userName . ' berhasil dihapus.']);
        }
        $this->closeDeleteModal();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->userToDelete = null;
    }

    public function render()
    {
        $users = User::where('id', '!=', auth()->id())
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.user-management', [
            'users' => $users
        ]);
    }
}
