<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン - TeamSync</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white h-screen overflow-hidden">
    <div class="flex w-full h-full">
        <!-- Left Column -->
        <div class="flex flex-col flex-1 p-8 bg-white overflow-y-auto">
            <!-- Logo -->
            <div class="mb-4">
                <img src="{{ asset('img/logo-retune.jpg') }}" alt="Retune" class="w-[232px] h-[66px] object-contain">
            </div>

            <!-- Content -->
            <div class="flex-1 flex flex-col justify-center px-16 max-w-[527px] mx-auto w-full">
                <!-- Title -->
                <h1 class="text-[40px] font-bold text-[#232323] leading-[110%] tracking-[-0.04em] mb-6">ログイン</h1>

                <!-- Form -->
                <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
                    @csrf

                    <!-- メールアドレス -->
                    <div class="relative">
                        <input 
                            type="email" 
                            name="email" 
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

                    <!-- パスワード -->
                    <div class="relative">
                        <input 
                            type="password" 
                            name="password" 
                            class="w-full h-[59px] px-4 text-lg border border-[#D9D9D9] rounded-[10px] focus:border-[#8060FF] focus:outline-none @error('password') border-red-500 @enderror"
                            required
                        >
                        <label class="absolute -top-[10.5px] left-3 px-1 bg-white text-sm font-medium text-[#9A9A9A]">
                            パスワード
                        </label>
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center gap-2">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            id="remember"
                            class="w-6 h-6 border-2 border-black rounded"
                        >
                        <label for="remember" class="text-[#232323] font-medium">
                            ログインしたままにする
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="w-full h-[54px] bg-gradient-to-r from-[#8060FF] to-[#30E0C0] rounded-[10px] text-white font-semibold text-lg hover:opacity-90 transition"
                    >
                        ログイン
                    </button>
                </form>

                <!-- Register Link -->
                <p class="text-center text-[#6C6C6C] text-lg mt-8">
                    ご利用は初めてですか? 
                    <a href="{{ route('register') }}" class="text-[#8060FF] hover:underline">新規登録はこちら</a>
                </p>
            </div>
        </div>

        <!-- Right Column -->
        <div class="flex-1 p-3 bg-white">
            <img src="{{ asset('img/retune-img.jpg') }}" alt="Background" class="w-full h-full object-cover rounded-[24px]">
        </div>
    </div>
</body>
</html>
