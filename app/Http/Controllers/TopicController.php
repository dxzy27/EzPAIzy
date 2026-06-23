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
     * Rename an existing topic.
     */
    public function update(Request $request, $topicName)
    {
        $validated = $request->validate([
            'new_name' => 'required|string|max:100',
            'type' => 'nullable|string|in:material,flashcard,quiz'
        ]);

        $newName = $validated['new_name'];

        if ($topicName === $newName) {
            return response()->json(['success' => true, 'new_name' => $newName]);
        }

        $query = Topic::where('user_id', auth()->id())->where('name', $topicName);
        if ($request->has('type') && $request->type !== null) {
            $query->where('type', $request->type);
        }
        $query->update(['name' => $newName]);

        // Update related content
        \App\Models\FlashcardSet::where('user_id', auth()->id())
            ->where('topic', $topicName)
            ->update(['topic' => $newName]);

        \App\Models\Quiz::where('user_id', auth()->id())
            ->where('topic', $topicName)
            ->update(['topic' => $newName]);

        \App\Models\Content::where('user_id', auth()->id())
            ->where('topic', $topicName)
            ->update(['topic' => $newName]);

        $redirectRoute = route('teacher.flashcard-sets.folder', ['topic' => $newName]);
        if ($request->type === 'quiz') {
            $redirectRoute = route('teacher.quizzes.folder', ['topic' => $newName]);
        } elseif ($request->type === 'material') {
            $redirectRoute = route('teacher.contents.folder', ['topic' => $newName]);
        }

        return response()->json([
            'success' => true,
            'new_name' => $newName,
            'redirect' => $redirectRoute
        ]);
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
