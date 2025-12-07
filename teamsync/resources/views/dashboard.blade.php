<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeamSync - ダッシュボード</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 overflow-hidden">
    <!-- ヘッダー -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <img src="{{ asset('img/logo-retune.jpg') }}" alt="Retune" class="w-[232px] h-[66px] object-contain">
            <div>
                @if(count($teamMembers) > 0)
                <button onclick="openModal()" id="surveyButton" class="bg-gradient-to-r from-[#8060FF] to-[#30E0C0] hover:opacity-90 text-white font-semibold py-2 px-6 rounded-[10px] transition">
                    アンケートに答える
                </button>
                <button onclick="toggleForm()" id="showFormBtnHeader" class="bg-gradient-to-r from-[#8060FF] to-[#30E0C0] hover:opacity-90 text-white font-semibold py-2 px-6 rounded-[10px] transition hidden">
                    新しい改善案を入力
                </button>
                @endif
            </div>
        </div>
    </header>

    <!-- スライダーコンテナ -->
    <div class="relative h-[calc(100vh-80px)] overflow-hidden">
        <!-- ダッシュボード画面 -->
        <div id="dashboardView" class="absolute inset-0 transition-transform duration-500 ease-in-out transform translate-x-0 overflow-y-auto">
            <!-- 右矢印ボタン -->
            <button onclick="switchView('improvement')" class="fixed right-4 top-1/2 transform -translate-y-1/2 bg-gradient-to-r from-[#8060FF] to-[#30E0C0] hover:opacity-90 text-white p-4 rounded-full shadow-lg z-10 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            <div class="container mx-auto px-4 py-8">
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        <!-- 週選択 -->
        <div class="bg-white rounded-[10px] shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">週の選択</h2>
                <div class="flex items-center gap-4">
                    @if(count($availableWeeks) > 0)
                    <a href="?week={{ max(1, $currentWeek - 1) }}" 
                       class="px-4 py-2 bg-gradient-to-r from-[#8060FF] to-[#30E0C0] hover:opacity-90 text-white font-semibold rounded-[10px] transition {{ $currentWeek <= 1 ? 'opacity-50 pointer-events-none' : '' }}">
                        ← 前の週
                    </a>
                    <div class="flex gap-2">
                        @foreach(range(1, max($availableWeeks ?: [1])) as $week)
                        <a href="?week={{ $week }}" 
                           class="px-4 py-2 rounded-[10px] transition {{ $week == $currentWeek ? 'bg-gradient-to-r from-[#8060FF] to-[#30E0C0] text-white font-semibold' : 'bg-gray-100 hover:bg-gray-200 text-[#6C6C6C]' }}">
                            第{{ $week }}週
                        </a>
                        @endforeach
                        @if(max($availableWeeks ?: [1]) < 10)
                        @foreach(range(max($availableWeeks ?: [1]) + 1, 10) as $week)
                        <a href="?week={{ $week }}" 
                           class="px-4 py-2 rounded-[10px] transition {{ $week == $currentWeek ? 'bg-gradient-to-r from-[#8060FF] to-[#30E0C0] text-white font-semibold' : 'bg-gray-50 hover:bg-gray-100 text-gray-400' }}">
                            第{{ $week }}週
                        </a>
                        @endforeach
                        @endif
                    </div>
                    <a href="?week={{ $currentWeek + 1 }}" 
                       class="px-4 py-2 bg-gradient-to-r from-[#8060FF] to-[#30E0C0] hover:opacity-90 text-white font-semibold rounded-[10px] transition">
                        次の週 →
                    </a>
                    @else
                    <p class="text-gray-500">まだアンケートの回答がありません</p>
                    @endif
                </div>
            </div>
            <div class="mt-4 text-center">
                <span class="text-2xl font-bold bg-gradient-to-r from-[#8060FF] to-[#30E0C0] bg-clip-text text-transparent">第{{ $currentWeek }}週</span>
                @if(in_array($currentWeek, $availableWeeks))
                <span class="ml-2 text-sm text-green-600">✓ 回答済み</span>
                @else
                <span class="ml-2 text-sm text-gray-400">未回答</span>
                @endif
            </div>
        </div>

        <!-- アラート（認識のズレ） -->
        @if(isset($maxVarianceIndex) && $maxVarianceIndex !== null && isset($questions[$maxVarianceIndex]))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-[10px]">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <p class="font-semibold text-red-800">認識のズレが最も大きい項目</p>
                    <p class="text-red-700 text-sm mt-1">
                        「<span class="font-bold">{{ $questions[$maxVarianceIndex]->content }}</span>」
                        （標準偏差: {{ number_format($questionVariances[$maxVarianceIndex], 2) }}）
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- レーダーチャートと分布図を横並び -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- レーダーチャート -->
            <div class="bg-white rounded-[10px] shadow-lg p-6">
                <h2 class="text-2xl font-semibold mb-4">チームメンバーのスコア比較</h2>
                <div class="w-full">
                    <canvas id="radarChart"></canvas>
                </div>
            </div>

            <!-- 分布図 -->
            <div class="bg-white rounded-[10px] shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold">質問別スコア分布</h2>
                    <div class="flex gap-2">
                        <a href="?week={{ $currentWeek }}&sort=default" 
                           class="px-4 py-2 rounded-[10px] transition {{ $sortMode === 'default' ? 'bg-gradient-to-r from-[#8060FF] to-[#30E0C0] text-white font-semibold' : 'bg-gray-100 hover:bg-gray-200 text-[#6C6C6C]' }}">
                            デフォルト順
                        </a>
                        <a href="?week={{ $currentWeek }}&sort=variance" 
                           class="px-4 py-2 rounded-[10px] transition {{ $sortMode === 'variance' ? 'bg-gradient-to-r from-[#8060FF] to-[#30E0C0] text-white font-semibold' : 'bg-gray-100 hover:bg-gray-200 text-[#6C6C6C]' }}">
                            ばらつき順
                        </a>
                    </div>
                </div>

                <div class="overflow-y-auto max-h-[600px]">
                    @foreach($distributionData as $index => $data)
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-medium">{{ $data['question'] }}</h3>
                            <div class="text-sm text-gray-600">
                                <span class="mr-4">平均: <span class="font-semibold">{{ number_format($data['mean'], 2) }}</span></span>
                                <span>標準偏差: <span class="font-semibold {{ $data['stdDev'] > 1.5 ? 'text-red-600' : ($data['stdDev'] > 1.0 ? 'text-orange-600' : 'text-green-600') }}">{{ number_format($data['stdDev'], 2) }}</span></span>
                            </div>
                        </div>
                        <div class="flex justify-between items-end h-48 border-b border-gray-300">
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
        </div>
    </div>
            </div>
        </div>

        <!-- 改善案入力画面 -->
        <div id="improvementView" class="absolute inset-0 transition-transform duration-500 ease-in-out transform translate-x-full bg-gray-100">
            <!-- 左矢印ボタン -->
            <button onclick="switchView('dashboard')" class="fixed left-4 top-1/2 transform -translate-y-1/2 bg-gradient-to-r from-[#8060FF] to-[#30E0C0] hover:opacity-90 text-white p-4 rounded-full shadow-lg z-10 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            
            <div class="h-full flex flex-col">
                <!-- ヘッダー部分 -->
                <div class="bg-white shadow-sm px-4 py-4">
                    <div class="container mx-auto flex justify-between items-center">
                        <img src="{{ asset('img/logo-retune.jpg') }}" alt="Retune" class="w-[232px] h-[66px] object-contain">
                        <button onclick="toggleImprovementForm()" class="bg-gradient-to-r from-[#8060FF] to-[#30E0C0] hover:opacity-90 text-white font-semibold py-2 px-6 rounded-[10px] transition">
                            新しい改善案を入力
                        </button>
                    </div>
                </div>

                <!-- コンテンツ部分 -->
                <div class="flex-1 overflow-y-auto">
                    <div class="container mx-auto px-4 py-8">
                        <!-- 週選択 -->
                        <div class="bg-white rounded-[10px] shadow-lg p-6 mb-8">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold">週の選択</h2>
                                <div class="flex items-center gap-4">
                                    <a href="?view=improvement&week={{ max(1, $currentWeek - 1) }}" 
                                       class="px-4 py-2 bg-gradient-to-r from-[#8060FF] to-[#30E0C0] hover:opacity-90 text-white font-semibold rounded-[10px] transition {{ $currentWeek <= 1 ? 'opacity-50 pointer-events-none' : '' }}">
                                        ← 前の週
                                    </a>
                                    <div class="flex gap-2">
                                        @php
                                            $improvementWeeks = $improvements->pluck('week_number')->unique()->sort()->values()->toArray();
                                            $maxWeek = max(array_merge($improvementWeeks, [$currentWeek]));
                                        @endphp
                                        @foreach(range(1, min($maxWeek + 2, 10)) as $week)
                                        <a href="?view=improvement&week={{ $week }}" 
                                           class="px-4 py-2 rounded-[10px] transition {{ $week == $currentWeek ? 'bg-gradient-to-r from-[#8060FF] to-[#30E0C0] text-white font-semibold' : (in_array($week, $improvementWeeks) ? 'bg-gray-100 hover:bg-gray-200 text-[#6C6C6C]' : 'bg-gray-50 hover:bg-gray-100 text-gray-400') }}">
                                            第{{ $week }}週
                                        </a>
                                        @endforeach
                                    </div>
                                    <a href="?view=improvement&week={{ $currentWeek + 1 }}" 
                                       class="px-4 py-2 bg-gradient-to-r from-[#8060FF] to-[#30E0C0] hover:opacity-90 text-white font-semibold rounded-[10px] transition">
                                        次の週 →
                                    </a>
                                </div>
                            </div>
                            <div class="mt-4 text-center">
                                <span class="text-2xl font-bold bg-gradient-to-r from-[#8060FF] to-[#30E0C0] bg-clip-text text-transparent">第{{ $currentWeek }}週</span>
                                @if(in_array($currentWeek, $improvementWeeks))
                                <span class="ml-2 text-sm text-green-600">✓ 入力済み</span>
                                @else
                                <span class="ml-2 text-sm text-gray-400">未入力</span>
                                @endif
                            </div>
                        </div>

                        <!-- 4象限表示 -->
                        @if($improvement && ($improvement->problem || $improvement->cause || $improvement->solution || $improvement->todo))
                        <div class="grid grid-cols-2 gap-4 h-[calc(100vh-350px)]">
                            <!-- 左上: 問題点 -->
                            <div class="bg-white rounded-[10px] shadow-lg p-6 overflow-y-auto">
                                <h3 class="text-xl font-bold text-[#232323] mb-4 pb-2 border-b-2 border-[#8060FF]">問題点</h3>
                                <p class="text-[#6C6C6C] whitespace-pre-wrap">{{ $improvement->problem ?? '未入力' }}</p>
                            </div>

                            <!-- 右上: 原因 -->
                            <div class="bg-white rounded-[10px] shadow-lg p-6 overflow-y-auto">
                                <h3 class="text-xl font-bold text-[#232323] mb-4 pb-2 border-b-2 border-[#30E0C0]">原因</h3>
                                <p class="text-[#6C6C6C] whitespace-pre-wrap">{{ $improvement->cause ?? '未入力' }}</p>
                            </div>

                            <!-- 左下: 改善方策 -->
                            <div class="bg-white rounded-[10px] shadow-lg p-6 overflow-y-auto">
                                <h3 class="text-xl font-bold text-[#232323] mb-4 pb-2 border-b-2 border-[#8060FF]">改善方策</h3>
                                <p class="text-[#6C6C6C] whitespace-pre-wrap">{{ $improvement->solution ?? '未入力' }}</p>
                            </div>

                            <!-- 右下: ToDo -->
                            <div class="bg-white rounded-[10px] shadow-lg p-6 overflow-y-auto">
                                <h3 class="text-xl font-bold text-[#232323] mb-4 pb-2 border-b-2 border-[#30E0C0]">ToDo</h3>
                                <p class="text-[#6C6C6C] whitespace-pre-wrap">{{ $improvement->todo ?? '未入力' }}</p>
                            </div>
                        </div>
                        @else
                        <div class="flex items-center justify-center h-[calc(100vh-350px)]">
                            <div class="text-center bg-white rounded-[10px] shadow-lg p-12">
                                <p class="text-[#9A9A9A] text-xl mb-4">第{{ $currentWeek }}週の改善案がまだ入力されていません</p>
                                <button onclick="toggleImprovementForm()" class="bg-gradient-to-r from-[#8060FF] to-[#30E0C0] hover:opacity-90 text-white font-semibold py-3 px-8 rounded-[10px] transition">
                                    改善案を入力する
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 入力フォームモーダル -->
            <div id="improvementFormModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
                <div class="bg-white rounded-[10px] shadow-xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center rounded-t-[10px]">
                        <h2 class="text-2xl font-semibold">第{{ $currentWeek }}週の改善案を入力</h2>
                        <button onclick="closeImprovementForm()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                    </div>
                    <form action="{{ route('improvement.store') }}" method="POST" class="p-6">
                        @csrf
                        <input type="hidden" name="team_id" value="{{ auth()->user()->team_id }}">
                        <input type="hidden" name="week_number" value="{{ $currentWeek }}">

                        <div class="grid grid-cols-2 gap-6">
                            <!-- 問題点 -->
                            <div>
                                <label class="block text-lg font-semibold text-gray-700 mb-2">問題点</label>
                                <textarea name="problem" rows="10" class="w-full px-4 py-3 border-[1.5px] border-[#D9D9D9] rounded-[10px] focus:border-[#8060FF] focus:outline-none" placeholder="チームが抱えている問題点を記入してください">{{ $improvement->problem ?? '' }}</textarea>
                            </div>

                            <!-- 原因 -->
                            <div>
                                <label class="block text-lg font-semibold text-gray-700 mb-2">原因</label>
                                <textarea name="cause" rows="10" class="w-full px-4 py-3 border-[1.5px] border-[#D9D9D9] rounded-[10px] focus:border-[#8060FF] focus:outline-none" placeholder="問題の原因を分析して記入してください">{{ $improvement->cause ?? '' }}</textarea>
                            </div>

                            <!-- 改善方策 -->
                            <div>
                                <label class="block text-lg font-semibold text-gray-700 mb-2">改善方策</label>
                                <textarea name="solution" rows="10" class="w-full px-4 py-3 border-[1.5px] border-[#D9D9D9] rounded-[10px] focus:border-[#8060FF] focus:outline-none" placeholder="具体的な改善方策を記入してください">{{ $improvement->solution ?? '' }}</textarea>
                            </div>

                            <!-- ToDo -->
                            <div>
                                <label class="block text-lg font-semibold text-gray-700 mb-2">ToDo</label>
                                <textarea name="todo" rows="10" class="w-full px-4 py-3 border-[1.5px] border-[#D9D9D9] rounded-[10px] focus:border-[#8060FF] focus:outline-none" placeholder="実行すべきタスクを記入してください">{{ $improvement->todo ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-6">
                            <button type="submit" class="flex-1 bg-gradient-to-r from-[#8060FF] to-[#30E0C0] hover:opacity-90 text-white font-bold py-3 px-4 rounded-[10px] transition">
                                保存
                            </button>
                            <button type="button" onclick="closeImprovementForm()" class="px-6 py-3 border-[1.5px] border-[#D9D9D9] rounded-[10px] hover:bg-gray-50 transition text-[#6C6C6C] font-semibold">
                                キャンセル
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- アンケートモーダル -->
    @if(count($teamMembers) > 0)
    <div id="surveyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-[10px] shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center rounded-t-[10px]">
                <h2 class="text-2xl font-semibold">今週のアンケート (<span class="bg-gradient-to-r from-[#8060FF] to-[#30E0C0] bg-clip-text text-transparent font-bold">第{{ $currentWeek }}週</span>)</h2>
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
                            class="flex-1 bg-gradient-to-r from-[#8060FF] to-[#30E0C0] hover:opacity-90 text-white font-bold py-3 px-4 rounded-[10px] transition">
                        回答を送信
                    </button>
                    <button type="button" onclick="closeModal()" 
                            class="px-6 py-3 border-[1.5px] border-[#D9D9D9] rounded-[10px] hover:bg-gray-50 transition text-[#6C6C6C] font-semibold">
                        キャンセル
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script>
        // スライダー切り替え
        function switchView(view) {
            const dashboardView = document.getElementById('dashboardView');
            const improvementView = document.getElementById('improvementView');
            const surveyButton = document.getElementById('surveyButton');
            const showFormBtnHeader = document.getElementById('showFormBtnHeader');

            if (view === 'dashboard') {
                dashboardView.classList.remove('translate-x-[-100%]');
                dashboardView.classList.add('translate-x-0');
                improvementView.classList.remove('translate-x-0');
                improvementView.classList.add('translate-x-full');
                if (surveyButton) surveyButton.classList.remove('hidden');
                if (showFormBtnHeader) showFormBtnHeader.classList.add('hidden');
            } else {
                dashboardView.classList.remove('translate-x-0');
                dashboardView.classList.add('translate-x-[-100%]');
                improvementView.classList.remove('translate-x-full');
                improvementView.classList.add('translate-x-0');
                if (surveyButton) surveyButton.classList.add('hidden');
                if (showFormBtnHeader) showFormBtnHeader.classList.remove('hidden');
            }
        }

        // 改善案フォームの表示/非表示切り替え
        function toggleForm() {
            toggleImprovementForm();
        }

        function toggleImprovementForm() {
            const modal = document.getElementById('improvementFormModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeImprovementForm() {
            const modal = document.getElementById('improvementFormModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        // ページ読み込み時の処理
        document.addEventListener('DOMContentLoaded', function() {
            // URLパラメータをチェック
            const urlParams = new URLSearchParams(window.location.search);
            const view = urlParams.get('view');
            
            // view=improvementの場合、改善案ページを表示
            if (view === 'improvement') {
                switchView('improvement');
                // フォームを閉じる
                const formContent = document.getElementById('improvementFormContent');
                const toggleBtn = document.getElementById('toggleFormBtn');
                const showFormBtnHeader = document.getElementById('showFormBtnHeader');
                const formContainer = document.getElementById('improvementForm');
                const surveyButton = document.getElementById('surveyButton');
                
                formContent.style.display = 'none';
                toggleBtn.textContent = '開く';
                if (showFormBtnHeader) showFormBtnHeader.classList.remove('hidden');
                formContainer.classList.add('hidden');
                if (surveyButton) surveyButton.classList.add('hidden');
            }
        });

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

            const maxVarianceIndex = {{ $maxVarianceIndex ?? 'null' }};

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
                                color: function(context) {
                                    return context.index === maxVarianceIndex ? '#dc2626' : '#333';
                                },
                                font: function(context) {
                                    return {
                                        size: 13,
                                        weight: context.index === maxVarianceIndex ? 'bold' : '500'
                                    };
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
