<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\FlashcardSet;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'materials');

        $contents = [];
        $flashcardSets = [];
        $questions = [];

        if ($tab === 'materials') {
            $contents = Content::with('teacher')->latest()->paginate(15);
        } elseif ($tab === 'flashcards') {
            $flashcardSets = FlashcardSet::with('user')->latest()->paginate(15);
        } elseif ($tab === 'questions') {
            $questions = \App\Models\Question::latest()->paginate(15);
        }

        return view('admin.moderation.index', compact('contents', 'flashcardSets', 'questions', 'tab'));
    }

    public function destroyQuestion(\App\Models\Question $question)
    {
        $text = \Illuminate\Support\Str::limit($question->question_text, 30);
        $question->delete();

        return redirect()->back()->with('success', "Question '{$text}' has been permanently deleted from the Question Bank.");
    }

    public function toggleContentFlag(Content $content)
    {
        $content->is_flagged = !$content->is_flagged;
        $content->save();

        $status = $content->is_flagged ? 'flagged as inappropriate' : 'approved';
        return redirect()->back()->with('success', "Material '{$content->title}' has been {$status}.");
    }

    public function destroyContent(Content $content)
    {
        $title = $content->title;
        $content->delete();

        return redirect()->back()->with('success', "Material '{$title}' has been permanently deleted.");
    }

    public function toggleFlashcardFlag(FlashcardSet $flashcardSet)
    {
        $flashcardSet->is_flagged = !$flashcardSet->is_flagged;
        $flashcardSet->save();

        $status = $flashcardSet->is_flagged ? 'flagged as inappropriate' : 'approved';
        return redirect()->back()->with('success', "Flashcard set '{$flashcardSet->title}' has been {$status}.");
    }

    public function destroyFlashcardSet(FlashcardSet $flashcardSet)
    {
        $title = $flashcardSet->title;
        $flashcardSet->delete();

        return redirect()->back()->with('success', "Flashcard set '{$title}' has been permanently deleted.");
    }
}
