<?php

namespace App\Http\Controllers;

use App\Models\Widget;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    /**
     * Show available widgets to add
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        // Available widget types
        $availableWidgets = [
            'progress' => ['name' => 'Quiz Progress', 'description' => 'View your quiz completion status'],
            'recent_quizzes' => ['name' => 'Recent Quizzes', 'description' => 'Display recently taken quizzes'],
            'announcements' => ['name' => 'Announcements', 'description' => 'Important announcements'],
            'stats' => ['name' => 'Statistics', 'description' => 'Your learning statistics'],
        ];
        
        // Get user's active widgets
        $activeWidgets = $user->widgets()->pluck('widget_type')->toArray();
        
        return view('student.widgets.index', compact('availableWidgets', 'activeWidgets'));
    }

    /**
     * Add widget to student's dashboard
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'widget_type' => 'required|string|in:progress,recent_quizzes,announcements,stats',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Get the highest position
        $maxPosition = $user->widgets()->max('position') ?? -1;

        // Create widget
        Widget::create([
            'user_id' => $user->id,
            'widget_type' => $validated['widget_type'],
            'position' => $maxPosition + 1,
        ]);

        return redirect()->route('student.widgets.index')
            ->with('success', 'Widget added to your dashboard!');
    }

    /**
     * Remove widget from dashboard
     */
    public function destroy(Widget $widget)
    {
        abort_if($widget->user_id !== auth()->id(), 403, 'Unauthorized');

        $widget->delete();

        return redirect()->route('student.widgets.index')
            ->with('success', 'Widget removed from your dashboard!');
    }

    /**
     * Show edit form for widget settings
     */
    public function edit(Widget $widget)
    {
        abort_if($widget->user_id !== auth()->id(), 403, 'Unauthorized');

        $settings = $widget->settings ?? [];

        return view('student.widgets.edit', compact('widget', 'settings'));
    }

    /**
     * Update widget settings
     */
    public function update(Request $request, Widget $widget)
    {
        abort_if($widget->user_id !== auth()->id(), 403, 'Unauthorized');

        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:20',
            'show_score' => 'nullable|boolean',
        ]);

        $widget->update([
            'settings' => $validated,
        ]);

        return redirect()->route('student.widgets.index')
            ->with('success', 'Widget settings updated!');
    }

    /**
     * Reorder widgets
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'positions' => 'required|array',
            'positions.*' => 'integer',
        ]);

        $user = auth()->user();
        $positions = $validated['positions'];

        foreach ($positions as $index => $widgetId) {
            Widget::where('id', $widgetId)
                ->where('user_id', $user->id)
                ->update(['position' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
