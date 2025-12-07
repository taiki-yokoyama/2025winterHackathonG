<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録 - TeamSync</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white h-screen overflow-hidden">
    <div class="flex w-full h-full">
        <!-- Left Column -->
        <div class="flex flex-col flex-1 p-8 bg-white overflow-y-auto">
            <!-- Title -->
            <div class="mb-8">
                <h1 class="text-[40px] font-bold text-[#232323] leading-[110%] tracking-[-0.04em]">新規登録</h1>
                <p class="text-[#6C6C6C] mt-2">TeamSyncへようこそ</p>
            </div>

            <!-- Content -->
            <div class="flex-1 flex flex-col justify-center px-16 max-w-[600px] mx-auto w-full">
                @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[10px] mb-6">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="flex flex-col gap-4">
                    @csrf

                    <!-- 名前 -->
                    <div class="relative">
                        <input 
                            type="text" 
                            name="name" 
                            id="name"
                            value="{{ old('name') }}"
                            class="w-full h-[59px] px-4 text-lg border-[1.5px] border-[#D9D9D9] rounded-[10px] focus:border-[#8060FF] focus:outline-none @error('name') border-red-500 @enderror"
                            required
                            autofocus
                        >
                        <label class="absolute -top-[10.5px] left-3 px-1 bg-white text-sm font-medium text-[#9A9A9A]">
                            名前
                        </label>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- メールアドレス -->
                    <div class="relative">
                        <input 
                            type="email" 
                            name="email" 
                            id="email"
                            value="{{ old('email') }}"
                            class="w-full h-[59px] px-4 text-lg border-[1.5px] border-[#D9D9D9] rounded-[10px] focus:border-[#8060FF] focus:outline-none @error('email') border-red-500 @enderror"
                            required
                        >
                        <label class="absolute -top-[10.5px] left-3 px-1 bg-white text-sm font-medium text-[#9A9A9A]">
                            メールアドレス
                        </label>
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- チームID -->
                    <div class="relative">
                        <select 
                            name="team_id" 
                            id="team_id"
                            class="w-full h-[59px] px-4 text-lg border-[1.5px] border-[#D9D9D9] rounded-[10px] focus:border-[#8060FF] focus:outline-none @error('team_id') border-red-500 @enderror"
                            required
                        >
                            <option value="">チームを選択してください</option>
                            @foreach(\App\Models\Team::all() as $team)
                            <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->name }}
                            </option>
                            @endforeach
                        </select>
                        <label class="absolute -top-[10.5px] left-3 px-1 bg-white text-sm font-medium text-[#9A9A9A]">
                            チーム
                        </label>
                        @error('team_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- アイコン画像 -->
                    <div>
                        <label class="block text-sm font-medium text-[#6C6C6C] mb-2">
                            アイコン画像
                        </label>
                        <div class="flex items-center gap-4">
                            <div id="preview" class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden border-2 border-[#D9D9D9]">
                                <span class="text-[#9A9A9A] text-sm">プレビュー</span>
                            </div>
                            <div class="flex-1">
                                <input 
                                    type="file" 
                                    name="icon" 
                                    id="icon"
                                    accept="image/*"
                                    class="w-full px-4 py-3 border-[1.5px] border-[#D9D9D9] rounded-[10px] focus:border-[#8060FF] focus:outline-none"
                                    onchange="previewImage(event)"
                                >
                                <p class="text-xs text-[#9A9A9A] mt-1">JPG, PNG, GIF形式（最大2MB）</p>
                            </div>
                        </div>
                    </div>

                    <!-- パスワード -->
                    <div class="relative">
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            class="w-full h-[59px] px-4 text-lg border-[1.5px] border-[#D9D9D9] rounded-[10px] focus:border-[#8060FF] focus:outline-none @error('password') border-red-500 @enderror"
                            required
                        >
                        <label class="absolute -top-[10.5px] left-3 px-1 bg-white text-sm font-medium text-[#9A9A9A]">
                            パスワード
                        </label>
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- パスワード確認 -->
                    <div class="relative">
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            id="password_confirmation"
                            class="w-full h-[59px] px-4 text-lg border-[1.5px] border-[#D9D9D9] rounded-[10px] focus:border-[#8060FF] focus:outline-none"
                            required
                        >
                        <label class="absolute -top-[10.5px] left-3 px-1 bg-white text-sm font-medium text-[#9A9A9A]">
                            パスワード（確認）
                        </label>
                    </div>

                    <!-- 登録ボタン -->
                    <button 
                        type="submit"
                        class="w-full h-[54px] bg-gradient-to-r from-[#8060FF] to-[#30E0C0] rounded-[10px] text-white font-semibold text-lg hover:opacity-90 transition mt-2"
                    >
                        登録
                    </button>
                </form>

                <!-- ログインリンク -->
                <p class="text-center text-[#6C6C6C] text-lg mt-8">
                    すでにアカウントをお持ちですか？ 
                    <a href="{{ route('login') }}" class="text-[#8060FF] hover:underline">ログイン</a>
                </p>
            </div>
        </div>

        <!-- Right Column -->
        <div class="flex-1 p-3 bg-white">
            <img src="{{ asset('img/retune-img.jpg') }}" alt="Background" class="w-full h-full object-cover rounded-[24px]">
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
