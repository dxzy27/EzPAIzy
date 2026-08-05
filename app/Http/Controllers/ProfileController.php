<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Show user profile
     */
    public function show()
    {
        $user = auth()->user();
        return view('profile.show', compact('user'));
    }

    /**
     * Show edit profile form
     */
    public function edit()
    {
        $user = auth()->user();
        $schoolClasses = \App\Models\SchoolClass::orderBy('name')->get();
        return view('profile.edit', compact('user', 'schoolClasses'));
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'class_name' => 'nullable|string|exists:school_classes,name',
        ]);

        $user->update($validated);

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully!');
    }
}
