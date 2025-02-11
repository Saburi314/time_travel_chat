@extends('layouts.app')

@section('title', 'ディベート')

@section('content')

<div class="chat-container">
    <div id="chat-area" class="border rounded p-3"></div>

    <form id="chat-form" action="#" class="chat-form">
        <textarea id="user-input" class="form-control" placeholder="メッセージを入力" rows="2"></textarea>
        <div class="button-container">
            <button type="submit" class="btn btn-success">送信</button>
            <button id="reset-button" class="btn btn-danger">ディベートをリセットする</button>
        </div>
    </form>
</div>

<!-- 🔹 JavaScript に値を渡す -->
<script>
    window.Opponents = @json(\App\Constants\Opponents::LIST);
    window.opponentKey = @json($opponentKey) || @json(\App\Constants\Opponents::DEFAULT);
</script>

@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/debate.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/debate.js') }}"></script>
@endsection
