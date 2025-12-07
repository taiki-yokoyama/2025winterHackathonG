<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_code' => 'required|string|exists:teams,code',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'ニックネームは必須です',
            'team_code.required' => 'チーム番号は必須です',
            'team_code.exists' => '指定されたチーム番号が存在しません',
            'email.required' => 'メールアドレスは必須です',
            'email.email' => '有効なメールアドレスを入力してください',
            'email.unique' => 'このメールアドレスは既に登録されています',
            'password.required' => 'パスワードは必須です',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードが一致しません',
        ]);

        // チーム番号からチームIDを取得
        $team = Team::where('code', $validated['team_code'])->first();

        // デフォルトアイコンをランダムに選択
        $defaultIcons = ['🔴', '🔵', '🟢', '🟡', '🟣', '🟠'];
        $randomIcon = $defaultIcons[array_rand($defaultIcons)];

        $user = User::create([
            'name' => $validated['name'],
            'team_id' => $team->id,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'icon_path' => $randomIcon,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', '登録が完了しました');
    }
}
