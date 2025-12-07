<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>アンケート - TeamSync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .score-button {
            transition: all 0.2s;
        }
        .score-button:hover {
            transform: scale(1.1);
        }
        .score-button.selected {
            transform: scale(1.15);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800">TeamSync アンケート</h1>
            <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                ダッシュボードへ
            </a>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="mb-6">
                <p class="text-lg text-gray-700">回答者: <span class="font-bold">{{ $currentUser->name }}</span> {{ $currentUser->icon_path }}</p>
                <p class="text-sm text-gray-500">Week {{ $weekNumber }}</p>
            </div>

            <form action="{{ route('answer.store') }}" method="POST" id="surveyForm">
                @csrf
                <input type="hidden" name="user_id" value="{{ $currentUser->id }}">
                <input type="hidden" name="week_number" value="{{ $weekNumber }}">

                @foreach($questions as $index => $question)
                <div class="mb-8 pb-8 border-b last:border-b-0">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700">
                        Q{{ $index + 1 }}: {{ $question->content }}
                    </h3>

                    <!-- スコア選択 -->
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-3">スコアを選択してください（1: 全くそう思わない 〜 5: とてもそう思う）</p>
                        <div class="flex gap-3 justify-center">
                            @for($score = 1; $score <= 5; $score++)
                            <button type="button" 
                                    class="score-button w-16 h-16 rounded-full border-2 font-bold text-lg
                                           {{ isset($existingAnswers[$question->id]) && $existingAnswers[$question->id]->score == $score 
                                              ? 'bg-blue-500 text-white border-blue-600 selected' 
                                              : 'bg-white text-gray-700 border-gray-300 hover:border-blue-400' }}"
                                    data-question-id="{{ $question->id }}"
                                    data-score="{{ $score }}"
                                    onclick="selectScore(this, {{ $question->id }}, {{ $score }})">
                                {{ $score }}
                            </button>
                            @endfor
                        </div>
                        <input type="hidden" 
                               name="answers[{{ $index }}][question_id]" 
                               value="{{ $question->id }}">
                        <input type="hidden" 
                               name="answers[{{ $index }}][score]" 
                               id="score_{{ $question->id }}"
                               value="{{ $existingAnswers[$question->id]->score ?? '' }}"
                               required>
                    </div>

                    <!-- コメント入力 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            理由・コメント
                        </label>
                        <textarea name="answers[{{ $index }}][comment]" 
                                  rows="3" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="このスコアを選んだ理由を教えてください..."
                                  required>{{ $existingAnswers[$question->id]->comment ?? '' }}</textarea>
                    </div>
                </div>
                @endforeach

                <div class="flex justify-center mt-8">
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-12 py-4 rounded-lg text-lg transition transform hover:scale-105">
                        回答を送信
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function selectScore(button, questionId, score) {
            // 同じ質問の他のボタンの選択を解除
            const buttons = document.querySelectorAll(`button[data-question-id="${questionId}"]`);
            buttons.forEach(btn => {
                btn.classList.remove('selected', 'bg-blue-500', 'text-white', 'border-blue-600');
                btn.classList.add('bg-white', 'text-gray-700', 'border-gray-300');
            });

            // クリックされたボタンを選択状態に
            button.classList.add('selected', 'bg-blue-500', 'text-white', 'border-blue-600');
            button.classList.remove('bg-white', 'text-gray-700', 'border-gray-300');

            // hidden inputに値を設定
            document.getElementById(`score_${questionId}`).value = score;
        }

        // フォーム送信前のバリデーション
        document.getElementById('surveyForm').addEventListener('submit', function(e) {
            const questions = {{ $questions->count() }};
            let allAnswered = true;

            for (let i = 0; i < questions; i++) {
                const scoreInput = document.querySelector(`input[name="answers[${i}][score]"]`);
                if (!scoreInput.value) {
                    allAnswered = false;
                    break;
                }
            }

            if (!allAnswered) {
                e.preventDefault();
                alert('すべての質問にスコアを選択してください。');
            }
        });
    </script>
</body>
</html>
