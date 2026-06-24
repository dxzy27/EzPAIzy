<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentNote;
use Illuminate\Http\Request;

class StudentNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'student']);
    }

    /**
     * Save or update a student note via AJAX.
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:100',
            'difficulty' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'resource_type' => 'nullable|string|max:50',
            'resource_id' => 'nullable|integer',
        ]);

        $note = StudentNote::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'resource_type' => $validated['resource_type'] ?? null,
                'resource_id' => $validated['resource_id'] ?? null,
                'topic' => $validated['topic'],
            ],
            [
                'difficulty' => $validated['difficulty'] ?? null,
                'title' => $validated['title'],
                'content' => $validated['content'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Note saved successfully!',
            'note' => $note,
        ]);
    }

    /**
     * Display all notes inside a specific topic folder.
     */
    public function showFolder($topic)
    {
        abort_if(auth()->user()->learning_style !== 'read_write', 403, 'Access denied.');

        $notes = StudentNote::where('user_id', auth()->id())
            ->where('topic', $topic)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('student.notes.folder', compact('notes', 'topic'));
    }

    /**
     * Delete a note.
     */
    public function destroy(StudentNote $note)
    {
        abort_if($note->user_id !== auth()->id(), 403);

        $note->delete();

        return back()->with('success', 'Note deleted successfully.');
    }
}
