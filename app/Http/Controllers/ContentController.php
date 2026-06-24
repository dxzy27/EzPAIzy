<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    /**
     * Display a listing of the contents.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $query = $user->contents();
        $flashcardsQuery = $user->flashcardSets();

        if ($request->has('topic') && $request->topic != '') {
            $query->where('topic', $request->topic);
            $flashcardsQuery->where('topic', $request->topic);
        }
        
        $contents = $query->latest()->paginate(10);
        $flashcardSets = $flashcardsQuery->latest()->get(); // Get flashcards for this topic
        $selectedTopic = $request->query('topic');

        // Fetch User topics only (type: material)
        $topics = \App\Models\Topic::where('user_id', auth()->id())->where('type', 'material')->get();
        
        return view('teacher.contents.index', compact('contents', 'flashcardSets', 'selectedTopic', 'topics'));
    }

    /**
     * Display content type selection page.
     */
    public function selection()
    {
        return view('teacher.contents.selection');
    }

    /**
     * Show the form for creating a new content.
     */
    public function create()
    {
        $topics = \App\Models\Topic::where('user_id', auth()->id())->where('type', 'material')->get();
        return view('teacher.contents.create', compact('topics'));
    }

    /**
     * Store a newly created content in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'topic' => 'required|string',
            'file' => 'nullable|file|max:204800', // 200MB max
        ]);

        $validated['content'] = $validated['content'] ?? '';

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('contents', 'public');
            $validated['file_path'] = $path;
            $validated['file_type'] = $request->file('file')->getClientOriginalExtension();
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $user->contents()->create($validated);

        return redirect()->route('teacher.contents.index', ['topic' => $request->topic])->with('success', 'Content created successfully');
    }

    /**
     * Display the specified content.
     */
    public function show(Content $content)
    {
        $this->authorize('view', $content);
        return view('teacher.contents.show', ['content' => $content]);
    }

    /**
     * Show the form for editing the specified content.
     */
    public function edit(Content $content)
    {
        $this->authorize('update', $content);
        $topics = \App\Models\Topic::where('user_id', auth()->id())->where('type', 'material')->get();
        return view('teacher.contents.edit', ['content' => $content, 'topics' => $topics]);
    }

    /**
     * Update the specified content in storage.
     */
    public function update(Request $request, Content $content)
    {
        $this->authorize('update', $content);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'topic' => 'required|string',
            'file' => 'nullable|file|max:204800', // 200MB max
        ]);

        $validated['content'] = $validated['content'] ?? '';

        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($content->file_path && Storage::disk('public')->exists($content->file_path)) {
                Storage::disk('public')->delete($content->file_path);
            }
            
            $path = $request->file('file')->store('contents', 'public');
            $validated['file_path'] = $path;
            $validated['file_type'] = $request->file('file')->getClientOriginalExtension();
        }

        $content->update($validated);

        return redirect()->route('teacher.contents.show', $content)->with('success', 'Content updated successfully');
    }

    /**
     * Remove the specified content from storage.
     */
    public function destroy(Content $content)
    {
        $this->authorize('delete', $content);
        $content->delete();

        return redirect()->route('teacher.contents.index')->with('success', 'Content deleted successfully');
    }

    /**
     * Display a folder's content (separate page).
     */
    public function folder($topic)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $contents = $user->contents()->where('topic', $topic)->latest()->paginate(10);
        $flashcardSets = $user->flashcardSets()->where('topic', $topic)->latest()->get();

        return view('teacher.contents.folder', compact('contents', 'flashcardSets', 'topic'));
    }
}
