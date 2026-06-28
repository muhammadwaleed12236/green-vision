<?php

namespace App\Http\Controllers\Rbac;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role');
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($roleId = $request->role_id) {
            $query->where('role_id', $roleId);
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $roles = Role::all();
        return view('rbac.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('rbac.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive',
        ]);

        $username = explode('@', $request->email)[0];
        $baseUsername = $username;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        User::create([
            'name' => $request->name,
            'username' => $username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'status' => $request->status,
            'usertype' => 'admin',
        ]);

        return redirect()->route('rbac.users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load('role');
        return view('rbac.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('rbac.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->only('name', 'email', 'phone', 'role_id', 'status');

        if ($request->filled('password')) {
            $request->validate(['password' => 'string']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return redirect()->route('rbac.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('rbac.users.index')->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);
        return redirect()->route('rbac.users.index')->with('success', 'User status updated.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate(['password' => 'required|string']);
        $user->update(['password' => Hash::make($request->password)]);
        return redirect()->route('rbac.users.index')->with('success', 'Password reset successfully.');
    }
}
