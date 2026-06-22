<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Display the revision page with all favorited contents
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get all favorited items directly from Favorite model

        
        $favorites = \App\Models\Favorite::where('student_id', $user->id)
            ->with(['content.teacher', 'flashcardSet.user']) // Corrected relationship names
            ->latest()
            ->get();

        return view('student.revision', compact('favorites'));
    }
    
    /**
     * Add content to favorites (AJAX)
     */
    public function store(Content $content)
    {
        $user = auth()->user();
        
        // Check if already favorited
        $exists = Favorite::where('student_id', $user->id)
            ->where('content_id', $content->id)
            ->exists();
        
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Already in your revision list'
            ], 400);
        }
        
        // Add to favorites
        Favorite::create([
            'student_id' => $user->id,
            'content_id' => $content->id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Added to revision list'
        ]);
    }
    
    /**
     * Remove content from favorites (AJAX)
     */
    public function destroy(Content $content)
    {
        $user = auth()->user();
        
        $deleted = Favorite::where('student_id', $user->id)
            ->where('content_id', $content->id)
            ->delete();
        
        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Removed from revision list'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Not found in revision list'
        ], 404);
    }

    /**
     * Add flashcard set to favorites (AJAX)
     */
    public function storeFlashcard(\App\Models\FlashcardSet $flashcardSet)
    {
        $user = auth()->user();
        
        $exists = Favorite::where('student_id', $user->id)
            ->where('flashcard_set_id', $flashcardSet->id)
            ->exists();
        
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Already in revision'], 400);
        }
        
        Favorite::create([
            'student_id' => $user->id,
            'flashcard_set_id' => $flashcardSet->id
        ]);
        
        return response()->json(['success' => true, 'message' => 'Added to revision']);
    }

    /**
     * Remove flashcard set from favorites (AJAX)
     */
    public function destroyFlashcard(\App\Models\FlashcardSet $flashcardSet)
    {
        $user = auth()->user();
        
        $deleted = Favorite::where('student_id', $user->id)
            ->where('flashcard_set_id', $flashcardSet->id)
            ->delete();
            
        if ($deleted) {
             return response()->json(['success' => true, 'message' => 'Removed from revision']);
        }
        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }
}
