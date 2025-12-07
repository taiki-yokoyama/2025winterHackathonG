<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Team;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        
        // テーブルクリア
        User::truncate();
        Team::truncate();
        Question::truncate();
        
        Schema::enableForeignKeyConstraints();

        // Team A作成
        $team = Team::create([
            'name' => 'Team A'
        ]);

        // ユーザー3名作成
        $users = [
            ['name' => '田中太郎', 'email' => 'tanaka@example.com', 'icon_path' => '🔴'],
            ['name' => '佐藤花子', 'email' => 'sato@example.com', 'icon_path' => '🔵'],
            ['name' => '鈴木一郎', 'email' => 'suzuki@example.com', 'icon_path' => '🟢'],
        ];

        foreach ($users as $userData) {
            User::create([
                'team_id' => $team->id,
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'icon_path' => $userData['icon_path'],
            ]);
        }

        // 質問5つ作成
        $questions = [
            'メンバーと本音で話せていますか？',
            'コーディングの進捗は順調ですか？',
            'チームの雰囲気は良いですか？',
            '優勝できる確信はありますか？',
            '健康状態は万全ですか？',
        ];

        foreach ($questions as $content) {
            Question::create(['content' => $content]);
        }
    }
}
