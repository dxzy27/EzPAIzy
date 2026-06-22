<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a newly created topic (folder).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'nullable|string|in:material,flashcard,quiz',
        ]);

        Topic::create([
            'name' => $validated['name'],
            'user_id' => auth()->id(),
            'is_system' => false,
            'type' => $validated['type'] ?? 'material',
        ]);

        return redirect()->back()->with('success', 'Folder created successfully!');
    }

    /**
     * Remove the specified topic.
     */
    public function destroy(Topic $topic)
    {
        abort_if($topic->user_id !== auth()->id() && !$topic->is_system, 403, 'Unauthorized');

        $topic->delete();

        return redirect()->back()->with('success', 'Folder deleted successfully!');
    }
}
