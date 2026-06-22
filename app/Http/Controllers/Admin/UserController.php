<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $query = User::whereIn('role', ['teacher', 'student']);

        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:teacher,student,admin',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'class_name' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'class_name' => $validated['class_name'] ?? null,
            'is_approved' => true, // Manually created users are approved by default
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'role' => 'required|string|in:teacher,student,admin',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'class_name' => 'nullable|string|max:255',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'class_name' => $validated['class_name'] ?? null,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    public function toggleSuspension(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot suspend yourself.');
        }

        $user->is_suspended = !$user->is_suspended;
        $user->save();

        $status = $user->is_suspended ? 'suspended' : 'unsuspended';

        return redirect()->back()->with('success', "User account has been {$status}.");
    }

    public function toggleApproval(User $user)
    {
        if ($user->role !== 'teacher') {
            return redirect()->back()->with('error', 'Only teacher accounts require verification.');
        }

        $user->is_approved = !$user->is_approved;
        $user->save();

        $status = $user->is_approved ? 'approved & verified' : 'unverified';

        return redirect()->back()->with('success', "Teacher verification status updated to {$status}.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->back()->with('success', 'Password reset successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }
}
