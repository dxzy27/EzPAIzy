<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\FlashcardSet;
use App\Models\Flashcard;
use App\Models\Topic;
use Illuminate\Http\Request;

class FlashcardSetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = FlashcardSet::where('user_id', auth()->id());

        if ($request->has('topic') && $request->topic != '') {
            $query->where('topic', $request->topic);
        }

        $flashcardSets = $query->latest()->paginate(12);
        $selectedTopic = $request->query('topic');

        // Fetch User topics only (type: flashcard)
        $topics = Topic::where('user_id', auth()->id())->where('type', 'flashcard')->get();

        return view('teacher.flashcard_sets.index', compact('flashcardSets', 'topics', 'selectedTopic'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $topics = Topic::where('user_id', auth()->id())->where('type', 'flashcard')->get();

        return view('teacher.flashcard_sets.create', compact('topics'));
    }

    /**
     * Store flashcard set and its cards.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'topic' => 'required|string',
            'description' => 'nullable|string',
            'flashcards' => 'required|array|min:1',
            'flashcards.*.term' => 'required|string',
            'flashcards.*.definition' => 'required|string',
        ]);

        $set = FlashcardSet::create([
            'title' => $validated['title'],
            'topic' => $validated['topic'],
            'description' => $validated['description'] ?? '',
            'user_id' => auth()->id(),
        ]);

        foreach ($validated['flashcards'] as $card) {
            Flashcard::create([
                'flashcard_set_id' => $set->id,
                'term' => $card['term'],
                'definition' => $card['definition'],
            ]);
        }

        return redirect()->route('teacher.flashcard-sets.folder', ['topic' => $set->topic])
            ->with('success', 'Flashcard set created successfully!');
    }

    /**
     * Show edit form.
     */
    public function edit(FlashcardSet $flashcardSet)
    {
        $topics = Topic::where('user_id', auth()->id())->where('type', 'flashcard')->get();

        return view('teacher.flashcard_sets.create', compact('flashcardSet', 'topics'));
    }

    /**
     * Update flashcard set.
     */
    public function update(Request $request, FlashcardSet $flashcardSet)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'topic' => 'required|string',
            'description' => 'nullable|string',
            'flashcards' => 'required|array|min:1',
            'flashcards.*.term' => 'required|string',
            'flashcards.*.definition' => 'required|string',
        ]);

        $flashcardSet->update([
            'title' => $validated['title'],
            'topic' => $validated['topic'],
            'description' => $validated['description'] ?? '',
        ]);

        // Delete existing and recreate
        $flashcardSet->flashcards()->delete();

        foreach ($validated['flashcards'] as $card) {
            Flashcard::create([
                'flashcard_set_id' => $flashcardSet->id,
                'term' => $card['term'],
                'definition' => $card['definition'],
            ]);
        }

        return redirect()->back()
            ->with('success', 'Flashcard set updated successfully!');
    }

    /**
     * Delete flashcard set.
     */
    public function destroy(FlashcardSet $flashcardSet)
    {
        $topic = $flashcardSet->topic;
        $flashcardSet->flashcards()->delete();
        $flashcardSet->delete();

        return redirect()->route('teacher.flashcard-sets.folder', ['topic' => $topic])
            ->with('success', 'Flashcard set deleted successfully!');
    }

    /**
     * Import flashcards from CSV.
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120', // 5MB
        ]);

        try {
            $path = $request->file('file')->getRealPath();
            $data = array_map('str_getcsv', file($path));
            
            $flashcards = [];
            foreach ($data as $index => $row) {
                // Ignore empty or header rows
                if (empty($row) || count($row) < 2) continue;
                
                $term = trim($row[0]);
                $definition = trim($row[1]);

                // Ignore header-like row (e.g. term, definition)
                if (strtolower($term) === 'term' && strtolower($definition) === 'definition') {
                    continue;
                }

                if (!empty($term) && !empty($definition)) {
                    $flashcards[] = [
                        'term' => $term,
                        'definition' => $definition
                    ];
                }
            }

            return response()->json(['flashcards' => $flashcards]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to parse file: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Display a folder's flashcards (separate page).
     */
    public function folder($topic)
    {
        $flashcardSets = FlashcardSet::where('user_id', auth()->id())
            ->where('topic', $topic)
            ->latest()
            ->paginate(12);

        return view('teacher.flashcard_sets.folder', compact('flashcardSets', 'topic'));
    }
}
