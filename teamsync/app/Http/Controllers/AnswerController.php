<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'question_id' => 'required|exists:questions,id',
            'score' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
            'week_number' => 'integer|min:1',
        ]);

        Answer::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'question_id' => $validated['question_id'],
                'week_number' => $validated['week_number'] ?? 1,
            ],
            [
                'score' => $validated['score'],
                'comment' => $validated['comment'],
            ]
        );

        return redirect()->route('dashboard')->with('success', '回答を保存しました');
    }
}
