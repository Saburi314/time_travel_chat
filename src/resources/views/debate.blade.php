@extends('layouts.app')

@section('title', 'ディベート')

@section('content')

<div class="chat-container">
    <div id="chat-area" class="border rounded p-3"></div>

    <form id="chat-form" class="chat-form">
        <textarea id="user-input" class="form-control" placeholder="メッセージを入力" rows="2"></textarea>
        <div class="button-container">
            <button id="send-button" type="submit" class="btn btn-success" disabled>送信</button>
            <button id="reset-button" type="button" class="btn btn-danger">ディベートをリセットする</button>
        </div>
    </form>
</div>

<!-- 🔹 JavaScript に値を渡す -->
<script>
    window.opponentId = @json($opponent->id);
</script>

@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/debate.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/debate.js') }}"></script>
@endsection
