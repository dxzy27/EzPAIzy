<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\FlashcardSet;
use App\Models\Question;
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
        
        $allTopics = \App\Models\Question::select('topic')->distinct()->pluck('topic')->filter()->toArray();

        if ($tab === 'materials') {
            $contents = Content::with('teacher')->latest()->paginate(15);
        } elseif ($tab === 'flashcards') {
            $flashcardSets = FlashcardSet::with('user')->latest()->paginate(15);
        } elseif ($tab === 'questions') {
            $query = \App\Models\Question::latest();
            
            if ($request->filled('topic')) {
                $query->where('topic', $request->input('topic'));
            }
            if ($request->filled('difficulty')) {
                $query->where('difficulty', $request->input('difficulty'));
            }
            
            $questions = $query->paginate(15);
        }

        return view('admin.moderation.index', compact('contents', 'flashcardSets', 'questions', 'tab', 'allTopics'));
    }

    public function destroyQuestion(\App\Models\Question $question)
    {
        $text = \Illuminate\Support\Str::limit($question->question_text, 30);
        $question->delete();

        return redirect()->back()->with('success', "Question '{$text}' has been permanently deleted from the Question Bank.");
    }

    public function toggleQuestionFlag(Question $question)
    {
        $question->is_flagged = !$question->is_flagged;
        $question->save();

        $status = $question->is_flagged ? 'flagged as inappropriate' : 'approved';
        $text = \Illuminate\Support\Str::limit($question->question_text, 30);
        return redirect()->back()->with('success', "Question '{$text}' has been {$status}.");
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
