@extends('layouts.app')

@section('title', '議論ページ')

@section('content')
<div class="text-center mb-4">
    <img src="{{ asset($opponent->image) }}" alt="{{ $opponent->name }}" class="img-fluid" style="max-width: 200px;">
    <h2>{{ $opponent->name }}</h2>
    <!-- <p class="lead">{{ $opponent->system_message }}</p> -->
</div>

<div class="chat-container">
    <div id="chat-area" class="border rounded p-3"></div>

    <form id="chat-form" class="chat-form">
        <textarea id="user-input" class="form-control" placeholder="メッセージを入力" rows="1"></textarea>
        <div class="button-container">
            <button id="send-button" type="submit" class="btn btn-success" disabled>送信</button>
            <button id="reset-button" type="button" class="btn btn-danger">会話をリセットする</button>
        </div>
    </form>
</div>

<!-- 🔹 JavaScript に値を渡す -->
<script>
    window.opponent = @json($opponent);
</script>

@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/debate.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/debate.js') }}"></script>
@endsection
