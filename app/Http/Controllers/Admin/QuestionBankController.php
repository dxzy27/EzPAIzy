<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionBank;
use Illuminate\Http\Request;

class QuestionBankController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $query = QuestionBank::query();

        if ($request->has('topic') && $request->topic != '') {
            $query->where('topic', $request->topic);
        }

        if ($request->has('difficulty') && $request->difficulty != '') {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('question_text', 'like', "%{$search}%");
        }

        $questions = $query->latest()->paginate(15)->withQueryString();

        return view('admin.question_bank.index', compact('questions'));
    }

    public function create()
    {
        return view('admin.question_bank.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|string|in:mcq,short_answer',
            'options' => 'nullable|array',
            'options.a' => 'required_if:type,mcq|string|nullable',
            'options.b' => 'required_if:type,mcq|string|nullable',
            'options.c' => 'required_if:type,mcq|string|nullable',
            'options.d' => 'required_if:type,mcq|string|nullable',
            'correct_answer' => 'required|string',
            'topic' => 'required|string|max:255',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'points' => 'required|integer|min:1',
        ]);

        QuestionBank::create([
            'question_text' => $validated['question_text'],
            'type' => $validated['type'],
            'options' => $validated['type'] === 'mcq' ? $validated['options'] : null,
            'correct_answer' => $validated['correct_answer'],
            'topic' => $validated['topic'],
            'difficulty' => $validated['difficulty'],
            'points' => $validated['points'],
        ]);

        return redirect()->route('admin.question-bank.index')->with('success', 'Question added to global bank successfully!');
    }

    public function edit(QuestionBank $question)
    {
        return view('admin.question_bank.edit', compact('question'));
    }

    public function update(Request $request, QuestionBank $question)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|string|in:mcq,short_answer',
            'options' => 'nullable|array',
            'options.a' => 'required_if:type,mcq|string|nullable',
            'options.b' => 'required_if:type,mcq|string|nullable',
            'options.c' => 'required_if:type,mcq|string|nullable',
            'options.d' => 'required_if:type,mcq|string|nullable',
            'correct_answer' => 'required|string',
            'topic' => 'required|string|max:255',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'points' => 'required|integer|min:1',
        ]);

        $question->update([
            'question_text' => $validated['question_text'],
            'type' => $validated['type'],
            'options' => $validated['type'] === 'mcq' ? $validated['options'] : null,
            'correct_answer' => $validated['correct_answer'],
            'topic' => $validated['topic'],
            'difficulty' => $validated['difficulty'],
            'points' => $validated['points'],
        ]);

        return redirect()->route('admin.question-bank.index')->with('success', 'Question updated in global bank successfully!');
    }

    public function destroy(QuestionBank $question)
    {
        $question->delete();
        return redirect()->route('admin.question-bank.index')->with('success', 'Question removed from global bank.');
    }
}
