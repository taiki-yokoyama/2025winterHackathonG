<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ダッシュボード - TeamSync</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-white h-screen overflow-hidden flex flex-col">
    <!-- ヘッダー -->
    <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <img src="{{ asset('img/logo-retune.jpg') }}" alt="Retune" class="h-12 object-contain">
            <h1 class="text-2xl font-bold text-[#232323]">ダッシュボード</h1>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-[#6C6C6C]">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-4 py-2 text-[#6C6C6C] hover:text-[#232323] transition">
                    ログアウト
                </button>
            </form>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <div class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-8 py-6">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-[10px] mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(count($teamMembers) > 0)
                <!-- 週選択とアンケートボタン -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        @foreach(range(1, max($availableWeeks ?: [1])) as $week)
                            <a href="?week={{ $week }}" 
                               class="px-4 py-2 rounded-[10px] transition {{ $week == $currentWeek ? 'bg-gradient-to-r from-[#8060FF] to-[#30E0C0] text-white' : 'bg-gray-100 text-[#6C6C6C] hover:bg-gray-200' }}">
                                第{{ $week }}週
                            </a>
                        @endforeach
                    </div>
                    <button onclick="openModal()" class="px-6 py-3 bg-gradient-to-r from-[#8060FF] to-[#30E0C0] text-white font-semibold rounded-[10px] hover:opacity-90 transition">
                        アンケートに答える
                    </button>
                </div>

                <!-- レーダーチャート -->
                <div class="bg-white border border-gray-200 rounded-[10px] p-6 mb-6">
                    <h2 class="text-xl font-bold text-[#232323] mb-4">チーム状況（レーダーチャート）</h2>
                    <div class="flex justify-center">
                        <canvas id="radarChart" width="400" height="400"></canvas>
                    </div>
                </div>

                <!-- 分布図 -->
                <div class="bg-white border border-gray-200 rounded-[10px] p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-[#232323]">質問別の回答分布</h2>
                        <div class="flex gap-2">
                            <a href="?week={{ $currentWeek }}&sort=default" 
                               class="px-4 py-2 rounded-[10px] transition {{ $sortMode == 'default' ? 'bg-gradient-to-r from-[#8060FF] to-[#30E0C0] text-white' : 'bg-gray-100 text-[#6C6C6C] hover:bg-gray-200' }}">
                                デフォルト
                            </a>
                            <a href="?week={{ $currentWeek }}&sort=variance" 
                               class="px-4 py-2 rounded-[10px] transition {{ $sortMode == 'variance' ? 'bg-gradient-to-r from-[#8060FF] to-[#30E0C0] text-white' : 'bg-gray-100 text-[#6C6C6C] hover:bg-gray-200' }}">
                                ばらつき順
                            </a>
                        </div>
                    </div>

                    @foreach($distributionData as $data)
                        <div class="mb-6 pb-6 border-b border-gray-200 last:border-b-0">
                            <h3 class="font-semibold text-[#232323] mb-3">{{ $data['question'] }}</h3>
                            <div class="flex gap-2">
                                @for($score = 1; $score <= 5; $score++)
                                    <div class="flex-1">
                                        <div class="text-center text-sm text-[#6C6C6C] mb-2">{{ $score }}</div>
                                        <div class="min-h-[100px] bg-gray-50 rounded-[10px] p-2 flex flex-col gap-2">
                                            @foreach($data['distribution'][$score] as $user)
                                                <div class="bg-white border border-gray-200 rounded-[10px] p-2 text-sm">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="text-lg">{{ $user['icon'] }}</span>
                                                        <span class="font-medium text-[#232323]">{{ $user['name'] }}</span>
                                                    </div>
                                                    @if($user['comment'])
                                                        <p class="text-[#6C6C6C] text-xs">{{ $user['comment'] }}</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-[#6C6C6C] text-lg">チームメンバーがいません</p>
                </div>
            @endif
        </div>
    </div>

    <!-- アンケートモーダル -->
    <div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-[10px] p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-[#232323]">アンケート</h2>
                <button onclick="closeModal()" class="text-[#6C6C6C] hover:text-[#232323] text-2xl">&times;</button>
            </div>

            <form method="POST" action="{{ route('answer.store') }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                <input type="hidden" name="week_number" value="{{ $currentWeek }}">

                @foreach($questions as $question)
                    <div class="mb-6">
                        <label class="block text-[#232323] font-medium mb-3">{{ $question->content }}</label>
                        
                        <!-- スコア選択 -->
                        <div class="flex gap-2 mb-3">
                            @for($score = 1; $score <= 5; $score++)
                                <label class="flex-1">
                                    <input type="radio" name="answers[{{ $question->id }}][score]" value="{{ $score }}" class="hidden peer" required>
                                    <div class="text-center py-3 border-2 border-gray-200 rounded-[10px] cursor-pointer peer-checked:border-[#8060FF] peer-checked:bg-gradient-to-r peer-checked:from-[#8060FF] peer-checked:to-[#30E0C0] peer-checked:text-white transition">
                                        {{ $score }}
                                    </div>
                                </label>
                            @endfor
                        </div>

                        <!-- コメント -->
                        <textarea 
                            name="answers[{{ $question->id }}][comment]" 
                            class="w-full px-4 py-3 border border-gray-200 rounded-[10px] focus:border-[#8060FF] focus:outline-none resize-none"
                            rows="3"
                            placeholder="コメントを入力してください"
                            required
                        ></textarea>
                    </div>
                @endforeach

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-[#8060FF] to-[#30E0C0] text-white font-semibold rounded-[10px] hover:opacity-90 transition">
                    送信
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }

        // レーダーチャート
        @if(count($teamMembers) > 0)
        const radarData = {
            labels: {!! json_encode(array_map(fn($q) => $q->content, $questions->toArray())) !!},
            datasets: {!! json_encode(array_map(function($data) {
                return [
                    'label' => $data['label'],
                    'data' => $data['data'],
                    'borderColor' => $data['color'],
                    'backgroundColor' => $data['color'] . '33',
                ];
            }, $radarData)) !!}
        };

        new Chart(document.getElementById('radarChart'), {
            type: 'radar',
            data: radarData,
            options: {
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 5,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        @endif
    </script>
</body>
</html>
