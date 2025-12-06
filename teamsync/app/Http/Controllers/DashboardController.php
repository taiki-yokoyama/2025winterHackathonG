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
        
        if (!$currentUser) {
            return view('dashboard', [
                'radar_data' => [],
                'distribution_data' => [],
                'questions' => [],
                'team_members' => [],
            ]);
        }

        $teamId = $currentUser->team_id;
        $weekNumber = 1;

        // 同じチームのメンバー全員
        $teamMembers = User::where('team_id', $teamId)->get();
        
        // 質問一覧
        $questions = Question::all();

        // レーダーチャート用データ
        $radarData = [];
        foreach ($teamMembers as $member) {
            $scores = [];
            foreach ($questions as $question) {
                $answer = Answer::where('user_id', $member->id)
                    ->where('question_id', $question->id)
                    ->where('week_number', $weekNumber)
                    ->first();
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
                $answer = Answer::where('user_id', $member->id)
                    ->where('question_id', $question->id)
                    ->where('week_number', $weekNumber)
                    ->first();
                
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
