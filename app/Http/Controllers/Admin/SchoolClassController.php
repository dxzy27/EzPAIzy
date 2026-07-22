<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classes = \App\Models\SchoolClass::orderBy('name')->get();
        return view('admin.classes.index', compact('classes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:school_classes,name',
        ]);

        \App\Models\SchoolClass::create($request->only('name'));
        return redirect()->route('admin.classes.index')->with('success', 'Class added successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $schoolClass = \App\Models\SchoolClass::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:school_classes,name,' . $schoolClass->id,
        ]);

        $schoolClass->update($request->only('name'));
        return redirect()->route('admin.classes.index')->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $schoolClass = \App\Models\SchoolClass::findOrFail($id);
        
        // Ensure no users are in this class before deleting
        $usersInClass = \App\Models\User::where('class_name', $schoolClass->name)->count();
        if ($usersInClass > 0) {
            return redirect()->route('admin.classes.index')->with('error', "Cannot delete class. {$usersInClass} user(s) are currently assigned to this class.");
        }

        $schoolClass->delete();
        return redirect()->route('admin.classes.index')->with('success', 'Class deleted successfully.');
    }
}
