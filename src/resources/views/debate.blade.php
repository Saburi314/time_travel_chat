@extends('layouts.app')

@section('title', 'ディベート')

@section('content')
<div id="chat-area" class="border rounded p-3 mb-4" style="height: 400px; overflow-y: scroll; background-color: #f9f9f9;">
    <!-- チャット内容がここに表示される -->
</div>
<form id="chat-form" class="d-flex">
    <input type="text" id="user-input" class="form-control me-2" placeholder="メッセージを入力" required>
    <button type="submit" class="btn btn-success">送信</button>
</form>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/debate.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/debate.js') }}"></script>
@endsection
