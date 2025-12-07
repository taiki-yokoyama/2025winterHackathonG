<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 認証されたユーザーを取得
        $currentUser = auth()->user();
        
        if (!$currentUser || !$currentUser->team_id) {
            return view('dashboard', [
                'radarData' => [],
                'distributionData' => [],
                'questions' => [],
                'teamMembers' => [],
                'currentWeek' => 1,
                'availableWeeks' => [],
            ]);
        }

        $teamId = $currentUser->team_id;
        
        // 利用可能な週のリストを取得
        $availableWeeks = Answer::whereHas('user', function($query) use ($teamId) {
            $query->where('team_id', $teamId);
        })
        ->distinct()
        ->pluck('week_number')
        ->sort()
        ->values()
        ->toArray();
        
        // 現在の週番号（リクエストから取得、デフォルトは最新週または1）
        $currentWeek = $request->input('week', !empty($availableWeeks) ? max($availableWeeks) : 1);

        // 同じチームのメンバー全員（Eager Loading）
        $teamMembers = User::where('team_id', $teamId)
            ->with(['answers' => function ($query) use ($currentWeek) {
                $query->where('week_number', $currentWeek);
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
        
        // メンバーごとに色を割り当て
        $memberColors = [];
        foreach ($teamMembers as $index => $member) {
            $hue = ($index * 360 / count($teamMembers));
            $memberColors[$member->id] = "hsl({$hue}, 70%, 50%)";
        }

        // レーダーチャート用データ
        $radarData = [];
        foreach ($teamMembers as $index => $member) {
            $scores = [];
            foreach ($questions as $question) {
                $answer = $answersMap[$member->id][$question->id] ?? null;
                $scores[] = $answer ? $answer->score : 0;
            }
            $radarData[] = [
                'label' => $member->name,
                'data' => $scores,
                'icon' => $member->icon_path,
                'color' => $memberColors[$member->id],
            ];
        }

        // ソートモード（デフォルトまたはばらつき順）
        $sortMode = $request->input('sort', 'default');

        // 分布図用データ（質問ごと、スコアごとにユーザーを配置）
        $distributionData = [];
        foreach ($questions as $question) {
            $distribution = [];
            $scores = [];
            
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
                        'color' => $memberColors[$member->id],
                    ];
                    $scores[] = $answer->score;
                }
            }
            
            // 標準偏差を計算
            $variance = 0;
            if (count($scores) > 0) {
                $mean = array_sum($scores) / count($scores);
                $variance = array_sum(array_map(function($score) use ($mean) {
                    return pow($score - $mean, 2);
                }, $scores)) / count($scores);
            }
            $stdDev = sqrt($variance);
            
            $distributionData[] = [
                'question' => $question->content,
                'distribution' => $distribution,
                'stdDev' => $stdDev,
                'mean' => count($scores) > 0 ? array_sum($scores) / count($scores) : 0,
            ];
        }

        // ばらつき順でソート
        if ($sortMode === 'variance') {
            usort($distributionData, function($a, $b) {
                return $b['stdDev'] <=> $a['stdDev'];
            });
        }

        return view('dashboard', compact('radarData', 'distributionData', 'questions', 'teamMembers', 'memberColors', 'currentWeek', 'availableWeeks', 'sortMode'));
    }
}
