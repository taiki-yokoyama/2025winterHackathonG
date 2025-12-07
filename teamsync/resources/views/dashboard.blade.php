<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeamSync - ダッシュボード</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <!-- ヘッダー -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">TeamSync</h1>
            @if(count($teamMembers) > 0)
            <button onclick="openModal()" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-lg transition">
                アンケートに答える
            </button>
            @endif
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- 週選択 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">週の選択</h2>
                <div class="flex items-center gap-4">
                    @if(count($availableWeeks) > 0)
                        <a href="?week={{ max(1, $currentWeek - 1) }}" 
                           class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition {{ $currentWeek <= 1 ? 'opacity-50 pointer-events-none' : '' }}">
                            ← 前の週
                        </a>
                        
                        <div class="flex gap-2">
                            @foreach(range(1, max($availableWeeks ?: [1])) as $week)
                                <a href="?week={{ $week }}" 
                                   class="px-4 py-2 rounded-lg transition {{ $week == $currentWeek ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300' }}">
                                    第{{ $week }}週
                                </a>
                            @endforeach
                            @if(max($availableWeeks ?: [1]) < 10)
                                @foreach(range(max($availableWeeks ?: [1]) + 1, 10) as $week)
                                    <a href="?week={{ $week }}" 
                                       class="px-4 py-2 rounded-lg transition {{ $week == $currentWeek ? 'bg-blue-500 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-400' }}">
                                        第{{ $week }}週
                                    </a>
                                @endforeach
                            @endif
                        </div>
                        
                        <a href="?week={{ $currentWeek + 1 }}" 
                           class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition">
                            次の週 →
                        </a>
                    @else
                        <p class="text-gray-500">まだアンケートの回答がありません</p>
                    @endif
                </div>
            </div>
            <div class="mt-4 text-center">
                <span class="text-2xl font-bold text-blue-600">第{{ $currentWeek }}週</span>
                @if(in_array($currentWeek, $availableWeeks))
                    <span class="ml-2 text-sm text-green-600">✓ 回答済み</span>
                @else
                    <span class="ml-2 text-sm text-gray-400">未回答</span>
                @endif
            </div>
        </div>

        <!-- レーダーチャート -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-semibold mb-4">チームメンバーのスコア比較</h2>
            <div class="max-w-2xl mx-auto">
                <canvas id="radarChart"></canvas>
            </div>
        </div>

        <!-- 分布図 -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold">質問別スコア分布</h2>
                <div class="flex gap-2">
                    <a href="?week={{ $currentWeek }}&sort=default" 
                       class="px-4 py-2 rounded-lg transition {{ $sortMode === 'default' ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300' }}">
                        デフォルト順
                    </a>
                    <a href="?week={{ $currentWeek }}&sort=variance" 
                       class="px-4 py-2 rounded-lg transition {{ $sortMode === 'variance' ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300' }}">
                        ばらつき順
                    </a>
                </div>
            </div>
            @foreach($distributionData as $index => $data)
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-lg font-medium">{{ $data['question'] }}</h3>
                        <div class="text-sm text-gray-600">
                            <span class="mr-4">平均: <span class="font-semibold">{{ number_format($data['mean'], 2) }}</span></span>
                            <span>標準偏差: <span class="font-semibold {{ $data['stdDev'] > 1.5 ? 'text-red-600' : ($data['stdDev'] > 1.0 ? 'text-orange-600' : 'text-green-600') }}">{{ number_format($data['stdDev'], 2) }}</span></span>
                        </div>
                    </div>
                    <div class="flex justify-between items-end h-64 border-b border-gray-300">
                        @for($score = 1; $score <= 5; $score++)
                            <div class="flex-1 flex flex-col items-center justify-end px-2">
                                <div class="flex flex-wrap justify-center gap-2 mb-2">
                                    @foreach($data['distribution'][$score] as $user)
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold" 
                                             style="background-color: {{ $user['color'] }};"
                                             title="{{ $user['name'] }}: {{ $user['comment'] ?? 'コメントなし' }}">
                                            {{ substr($user['name'], 0, 1) }}
                                        </div>
                                    @endforeach
                                </div>
                                <div class="text-center font-semibold">{{ $score }}</div>
                            </div>
                        @endfor
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- アンケートモーダル -->
    @if(count($teamMembers) > 0)
    <div id="surveyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
                <h2 class="text-2xl font-semibold">今週のアンケート</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            
            <form action="{{ route('answer.store') }}" method="POST" class="p-6 space-y-6">
                @csrf
                <input type="hidden" name="week_number" value="{{ $currentWeek }}">
                
                <!-- ユーザー選択 -->
                <div>
                    <label class="block text-sm font-medium mb-2">回答者</label>
                    <select name="user_id" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @foreach($teamMembers as $member)
                            <option value="{{ $member->id }}">{{ $member->icon_path }} {{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 質問ごとの回答 -->
                @foreach($questions as $question)
                    <div class="border-t pt-4">
                        <label class="block text-sm font-medium mb-3">{{ $question->content }}</label>
                        
                        <!-- スコア選択 -->
                        <div class="flex gap-4 mb-3">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="score_{{ $question->id }}" value="{{ $i }}" required 
                                           class="mr-2 w-4 h-4 text-blue-600 focus:ring-blue-500">
                                    <span class="text-lg">{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                        
                        <!-- コメント入力 -->
                        <textarea name="comment_{{ $question->id }}" required 
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                  rows="2" placeholder="コメントを入力してください"></textarea>
                    </div>
                @endforeach

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition">
                        回答を送信
                    </button>
                    <button type="button" onclick="closeModal()" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        キャンセル
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script>
        // モーダル制御
        function openModal() {
            document.getElementById('surveyModal').classList.remove('hidden');
            document.getElementById('surveyModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('surveyModal').classList.add('hidden');
            document.getElementById('surveyModal').classList.remove('flex');
        }

        // モーダル外クリックで閉じる
        document.getElementById('surveyModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // レーダーチャートの設定
        const radarData = @json($radarData);
        const questions = @json($questions);
        
        const ctx = document.getElementById('radarChart').getContext('2d');
        const radarChart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: questions.map(q => q.content),
                datasets: radarData.map((member) => ({
                    label: member.label,
                    data: member.data,
                    borderColor: member.color,
                    backgroundColor: member.color.replace('hsl', 'hsla').replace(')', ', 0.2)'),
                }))
            },
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
    </script>
</body>
</html>
