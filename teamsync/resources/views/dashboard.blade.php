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
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold cursor-pointer hover:scale-110 transition user-icon overflow-hidden border-2" 
                                 style="background-color: {{ $user['color'] }}; border-color: {{ $user['color'] }};"
                                 data-name="{{ $user['name'] }}"
                                 data-comment="{{ $user['comment'] ?? 'コメントなし' }}">
                                @if($user['icon'] && !preg_match('/[\x{1F300}-\x{1F9FF}]/u', $user['icon']))
                                    <img src="{{ asset('storage/' . $user['icon']) }}" alt="{{ $user['name'] }}" class="w-full h-full object-cover">
                                @else
                                    {{ $user['icon'] ?? substr($user['name'], 0, 1) }}
                                @endif
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
                <h2 class="text-2xl font-semibold">今週のアンケート (第{{ $currentWeek }}週)</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            <form action="{{ route('answer.store') }}" method="POST" class="p-6 space-y-6">
                @csrf
                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                <input type="hidden" name="week_number" value="{{ $currentWeek }}">

                <!-- 質問ごとの回答 -->
                @foreach($questions as $index => $question)
                <div class="border-t pt-4">
                    <label class="block text-sm font-medium mb-3">{{ $question->content }}</label>
                    
                    <!-- スコア選択 -->
                    <div class="flex gap-4 mb-3">
                        @for($i = 1; $i <= 5; $i++)
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="answers[{{ $index }}][score]" value="{{ $i }}" required 
                                   class="mr-2 w-4 h-4 text-blue-600 focus:ring-blue-500">
                            <span class="text-lg">{{ $i }}</span>
                        </label>
                        @endfor
                    </div>
                    
                    <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $question->id }}">
                    
                    <!-- コメント入力 -->
                    <label class="block text-sm text-gray-600 mb-2">なぜその数値にしたのですか？</label>
                    <textarea name="answers[{{ $index }}][comment]" required 
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                              rows="3" 
                              placeholder="例：チームの雰囲気が良く、みんなで協力できているため"></textarea>
                </div>
                @endforeach

                <div class="flex gap-3 pt-4">
                    <button type="submit" 
                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition">
                        回答を送信
                    </button>
                    <button type="button" onclick="closeModal()" 
                            class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
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

        // ツールチップ機能
        document.addEventListener('DOMContentLoaded', function() {
            const userIcons = document.querySelectorAll('.user-icon');
            let tooltip = null;

            userIcons.forEach(icon => {
                icon.addEventListener('mouseenter', function(e) {
                    const name = this.dataset.name;
                    const comment = this.dataset.comment;
                    
                    tooltip = document.createElement('div');
                    tooltip.className = 'fixed bg-gray-900 text-white text-sm rounded-lg px-3 py-2 z-50 max-w-xs shadow-lg';
                    tooltip.innerHTML = `<strong>${name}</strong><br>${comment}`;
                    document.body.appendChild(tooltip);
                    
                    updateTooltipPosition(e, tooltip);
                });

                icon.addEventListener('mousemove', function(e) {
                    if (tooltip) {
                        updateTooltipPosition(e, tooltip);
                    }
                });

                icon.addEventListener('mouseleave', function() {
                    if (tooltip) {
                        tooltip.remove();
                        tooltip = null;
                    }
                });
            });

            function updateTooltipPosition(e, tooltip) {
                const x = e.clientX + 10;
                const y = e.clientY + 10;
                tooltip.style.left = x + 'px';
                tooltip.style.top = y + 'px';
            }
        });

        // レーダーチャート描画
        const radarData = @json($radarData);
        const questions = @json(collect($questions)->pluck('content'));

        // HSL色をRGBAに変換する関数
        function hslToRgba(hsl, alpha) {
            const match = hsl.match(/hsl\((\d+),\s*(\d+)%,\s*(\d+)%\)/);
            if (!match) return `rgba(100, 100, 100, ${alpha})`;
            
            const h = parseInt(match[1]);
            const s = parseInt(match[2]) / 100;
            const l = parseInt(match[3]) / 100;
            
            const c = (1 - Math.abs(2 * l - 1)) * s;
            const x = c * (1 - Math.abs((h / 60) % 2 - 1));
            const m = l - c / 2;
            
            let r, g, b;
            if (h < 60) { r = c; g = x; b = 0; }
            else if (h < 120) { r = x; g = c; b = 0; }
            else if (h < 180) { r = 0; g = c; b = x; }
            else if (h < 240) { r = 0; g = x; b = c; }
            else if (h < 300) { r = x; g = 0; b = c; }
            else { r = c; g = 0; b = x; }
            
            r = Math.round((r + m) * 255);
            g = Math.round((g + m) * 255);
            b = Math.round((b + m) * 255);
            
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }

        if (radarData.length > 0) {
            const datasets = radarData.map((member, memberIndex) => ({
                label: member.label,
                data: member.data,
                comments: member.comments,
                backgroundColor: hslToRgba(member.color, 0.2),
                borderColor: member.color,
                borderWidth: 3,
                pointBackgroundColor: member.color,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: member.color,
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2
            }));

            const ctx = document.getElementById('radarChart').getContext('2d');
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: questions,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 5,
                            min: 0,
                            ticks: {
                                stepSize: 1,
                                backdropColor: 'transparent',
                                color: '#666',
                                font: {
                                    size: 12
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)',
                                lineWidth: 1
                            },
                            angleLines: {
                                color: 'rgba(0, 0, 0, 0.1)',
                                lineWidth: 1
                            },
                            pointLabels: {
                                color: '#333',
                                font: {
                                    size: 13,
                                    weight: '500'
                                },
                                padding: 10
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 13
                                },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 12
                            },
                            cornerRadius: 6,
                            callbacks: {
                                label: function(context) {
                                    const datasetIndex = context.datasetIndex;
                                    const dataIndex = context.dataIndex;
                                    const dataset = context.chart.data.datasets[datasetIndex];
                                    const score = dataset.data[dataIndex];
                                    const comment = dataset.comments[dataIndex];
                                    
                                    return [
                                        `${dataset.label}: ${score}`,
                                        comment ? `理由: ${comment}` : '理由: なし'
                                    ];
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
