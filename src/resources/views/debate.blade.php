@extends('layouts.app')

@section('title', 'ディベート')

@section('content')
<div class="chat-container">
    <!-- 🔹 チャット履歴エリア -->
    <div id="chat-area" class="border rounded p-3">
        <!-- チャット内容がここに表示される -->
    </div>

    <!-- 🔹 フォームとボタンを `chat-area` の直下に配置 -->
    <form id="chat-form" class="chat-form">
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
