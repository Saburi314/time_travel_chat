@extends('layouts.app')

@section('title', 'ディベート')

@section('content')

@php
    // 議論相手のデータ
    $opponents = [
        'hiroyuki' => [
            'name' => '西村博之',
            'image' => '/images/hiroyuki_icon.webp',
            'prompt' => "あなたは **西村博之** です。\n揚げ足取りと煽るのが得意で…"
        ],
        'matsuko' => [
            'name' => 'マツコ・デラックス',
            'image' => '/images/matsuko_icon.webp',
            'prompt' => "あなたは **マツコ・デラックス** です。\n歯に衣着せぬ発言で…"
        ],
        'takafumi' => [
            'name' => '堀江貴文',
            'image' => '/images/takafumi_icon.webp',
            'prompt' => "あなたは **堀江貴文** です。\nビジネスの視点から…"
        ]
    ];

    // デフォルトは西村博之
    $opponentKey = request()->query('opponent', 'hiroyuki');
    $opponent = $opponents[$opponentKey] ?? $opponents['hiroyuki'];
@endphp

<div class="chat-container">
    <!-- 🔹 チャット履歴エリア -->
    <div id="chat-area" class="border rounded p-3">
        <!-- チャット内容がここに表示される -->
    </div>

    <!-- 🔹 フォームとボタンを `chat-area` の直下に配置 -->
    <form id="chat-form" action="#" class="chat-form">
        <textarea id="user-input" class="form-control" placeholder="メッセージを入力" rows="2"></textarea>
        <div class="button-container">
            <button type="submit" class="btn btn-success">送信</button>
            <button id="reset-button" class="btn btn-danger">ディベートをリセットする</button>
        </div>
    </form>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/debate.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/debate.js') }}"></script>
@endsection
