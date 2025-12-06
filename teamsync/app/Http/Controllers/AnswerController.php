<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\User;
use App\Models\Question;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function index()
    {
        // デモ用に最初のユーザーでログインしていると仮定
        $currentUser = User::first();
        $questions = Question::all();
        $weekNumber = 1;

        // 既存の回答を取得
        $existingAnswers = [];
        if ($currentUser) {
            foreach ($questions as $question) {
                $answer = Answer::where('user_id', $currentUser->id)
                    ->where('question_id', $question->id)
                    ->where('week_number', $weekNumber)
                    ->first();
                if ($answer) {
                    $existingAnswers[$question->id] = $answer;
                }
            }
        }

        return view('survey', compact('currentUser', 'questions', 'existingAnswers', 'weekNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.score' => 'required|integer|min:1|max:5',
            'answers.*.comment' => 'required|string',
            'week_number' => 'integer|min:1',
        ]);

        $weekNumber = $validated['week_number'] ?? 1;

        foreach ($validated['answers'] as $answerData) {
            Answer::updateOrCreate(
                [
                    'user_id' => $validated['user_id'],
                    'question_id' => $answerData['question_id'],
                    'week_number' => $weekNumber,
                ],
                [
                    'score' => $answerData['score'],
                    'comment' => $answerData['comment'],
                ]
            );
        }

        return redirect()->route('dashboard')->with('success', '回答を保存しました！');
    }
}
