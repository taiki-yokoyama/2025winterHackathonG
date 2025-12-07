<?php

namespace App\Http\Controllers;

use App\Models\Improvement;
use Illuminate\Http\Request;

class ImprovementController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'week_number' => 'required|integer|min:1',
            'problem' => 'nullable|string',
            'cause' => 'nullable|string',
            'solution' => 'nullable|string',
            'todo' => 'nullable|string',
        ]);

        Improvement::updateOrCreate(
            [
                'team_id' => $validated['team_id'],
                'week_number' => $validated['week_number'],
            ],
            [
                'problem' => $validated['problem'],
                'cause' => $validated['cause'],
                'solution' => $validated['solution'],
                'todo' => $validated['todo'],
            ]
        );

        return redirect()->route('dashboard', ['week' => $validated['week_number'], 'view' => 'improvement'])
            ->with('success', '改善案を保存しました！');
    }
}
