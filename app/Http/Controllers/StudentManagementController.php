<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class StudentManagementController extends Controller
{
    /**
     * List all students with their progress
     */
    public function index()
    {
        $teacher = auth()->user();
        
        $query = User::where('role', 'student');
        
        // If teacher is assigned to a specific class, strictly filter students
        if (!empty($teacher->class_name)) {
            $query->where('class_name', $teacher->class_name);
        }
        
        $students = $query->paginate(10);
        
        return view('teacher.students.index', compact('students', 'teacher'));
    }

    /**
     * Show form to create new student
     */
    public function create()
    {
        return view('teacher.students.create');
    }

    /**
     * Store new student
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'class_name' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['role'] = 'student';
        $student = User::create($validated);

        return redirect()->route('teacher.students.show', $student)
            ->with('success', 'Student added successfully!');
    }

    /**
     * Show student details and progress
     */
    public function show(User $student)
    {
        abort_if($student->role !== 'student', 403, 'This user is not a student');

        $teacher = auth()->user();
        $progress = $student->progress()->with('quiz')->paginate(5);

        return view('teacher.students.show', compact('student', 'teacher', 'progress'));
    }

    /**
     * Show form to edit student
     */
    public function edit(User $student)
    {
        abort_if($student->role !== 'student', 403, 'This user is not a student');

        return view('teacher.students.edit', compact('student'));
    }

    /**
     * Update student
     */
    public function update(Request $request, User $student)
    {
        abort_if($student->role !== 'student', 403, 'This user is not a student');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $student->id,
            'phone' => 'nullable|string|max:20',
            'class_name' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        $student->update($validated);

        return redirect()->route('teacher.students.index')
            ->with('success', 'Student updated successfully!');
    }

    /**
     * Delete student
     */
    public function destroy(User $student)
    {
        abort_if($student->role !== 'student', 403, 'This user is not a student');

        $studentName = $student->name;
        $student->delete();

        return redirect()->route('teacher.students.index')
            ->with('success', "Student '$studentName' deleted successfully!");
    }
}
