<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // デモ用に最初のユーザーでログインしていると仮定
        $currentUser = User::first();
        
        if (!$currentUser || !$currentUser->team_id) {
            return view('dashboard', [
                'radarData' => [],
                'distributionData' => [],
                'questions' => [],
                'teamMembers' => [],
            ]);
        }

        $teamId = $currentUser->team_id;
        $weekNumber = 1;

        // 同じチームのメンバー全員（Eager Loading）
        $teamMembers = User::where('team_id', $teamId)
            ->with(['answers' => function ($query) use ($weekNumber) {
                $query->where('week_number', $weekNumber);
            }])
            ->get();
        
        // 質問一覧
        $questions = Question::all();

        // 回答データを事前に取得してマップ化（N+1問題を回避）
        $answersMap = [];
        foreach ($teamMembers as $member) {
            foreach ($member->answers as $answer) {
                $answersMap[$member->id][$answer->question_id] = $answer;
            }
        }

        // レーダーチャート用データ
        $radarData = [];
        foreach ($teamMembers as $member) {
            $scores = [];
            foreach ($questions as $question) {
                $answer = $answersMap[$member->id][$question->id] ?? null;
                $scores[] = $answer ? $answer->score : 0;
            }
            $radarData[] = [
                'label' => $member->name,
                'data' => $scores,
                'icon' => $member->icon_path,
            ];
        }

        // 分布図用データ（質問ごと、スコアごとにユーザーを配置）
        $distributionData = [];
        foreach ($questions as $question) {
            $distribution = [];
            for ($score = 1; $score <= 5; $score++) {
                $distribution[$score] = [];
            }
            
            foreach ($teamMembers as $member) {
                $answer = $answersMap[$member->id][$question->id] ?? null;
                
                if ($answer) {
                    $distribution[$answer->score][] = [
                        'name' => $member->name,
                        'icon' => $member->icon_path,
                        'comment' => $answer->comment,
                    ];
                }
            }
            
            $distributionData[] = [
                'question' => $question->content,
                'distribution' => $distribution,
            ];
        }

        return view('dashboard', compact('radarData', 'distributionData', 'questions', 'teamMembers'));
    }
}
