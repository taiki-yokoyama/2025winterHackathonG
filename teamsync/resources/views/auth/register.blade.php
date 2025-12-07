<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録 - TeamSync</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-800">TeamSync</h1>
                <p class="text-gray-600 mt-2">新規登録</p>
            </div>

            @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul>
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                @csrf

                <!-- 名前 -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        名前
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name"
                        value="{{ old('name') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                        autofocus
                    >
                </div>

                <!-- メールアドレス -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        メールアドレス
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email"
                        value="{{ old('email') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    >
                </div>

                <!-- チームID -->
                <div class="mb-4">
                    <label for="team_id" class="block text-sm font-medium text-gray-700 mb-2">
                        チームを選択
                    </label>
                    <select 
                        name="team_id" 
                        id="team_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    >
                        <option value="">チームを選択してください</option>
                        @foreach(\App\Models\Team::all() as $team)
                        <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                            {{ $team->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- アイコン画像 -->
                <div class="mb-4">
                    <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
                        アイコン画像
                    </label>
                    <div class="flex items-center gap-4">
                        <div id="preview" class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                            <span class="text-gray-400 text-sm">プレビュー</span>
                        </div>
                        <div class="flex-1">
                            <input 
                                type="file" 
                                name="icon" 
                                id="icon"
                                accept="image/*"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                onchange="previewImage(event)"
                            >
                            <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF形式の画像をアップロードしてください</p>
                        </div>
                    </div>
                </div>

                <!-- パスワード -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        パスワード
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    >
                </div>

                <!-- パスワード確認 -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        パスワード（確認）
                    </label>
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        id="password_confirmation"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    >
                </div>

                <!-- 登録ボタン -->
                <button 
                    type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-lg transition"
                >
                    登録
                </button>
            </form>

            <!-- ログインリンク -->
            <p class="text-center text-gray-600 mt-6">
                すでにアカウントをお持ちですか？ 
                <a href="{{ route('login') }}" class="text-blue-500 hover:underline">ログイン</a>
            </p>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover" alt="プレビュー">`;
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>
