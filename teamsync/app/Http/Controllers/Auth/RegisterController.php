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
            'team_id' => 'required|exists:teams,id',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => '名前は必須です',
            'team_id.required' => 'チームは必須です',
            'team_id.exists' => '指定されたチームが存在しません',
            'email.required' => 'メールアドレスは必須です',
            'email.email' => '有効なメールアドレスを入力してください',
            'email.unique' => 'このメールアドレスは既に登録されています',
            'password.required' => 'パスワードは必須です',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードが一致しません',
            'icon.image' => 'アイコンは画像ファイルである必要があります',
            'icon.mimes' => 'アイコンはJPEG、PNG、JPG、GIF形式である必要があります',
            'icon.max' => 'アイコンのサイズは2MB以下である必要があります',
        ]);

        // アイコン画像の保存
        $iconPath = null;
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('icons', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'team_id' => $validated['team_id'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'icon_path' => $iconPath,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', '登録が完了しました');
    }
}
