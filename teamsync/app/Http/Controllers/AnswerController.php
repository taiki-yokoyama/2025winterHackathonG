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
            'comment' => 'required|string|max:1000',
            'week_number' => 'nullable|integer|min:1',
        ], [
            'user_id.required' => 'ユーザーIDは必須です',
            'user_id.exists' => '指定されたユーザーが存在しません',
            'question_id.required' => '質問IDは必須です',
            'question_id.exists' => '指定された質問が存在しません',
            'score.required' => 'スコアは必須です',
            'score.integer' => 'スコアは整数で入力してください',
            'score.min' => 'スコアは1以上で入力してください',
            'score.max' => 'スコアは5以下で入力してください',
            'comment.required' => 'コメントは必須です',
            'comment.max' => 'コメントは1000文字以内で入力してください',
            'week_number.integer' => '週番号は整数で入力してください',
            'week_number.min' => '週番号は1以上で入力してください',
        ]);

        try {
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
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '回答の保存に失敗しました');
        }
    }
}
