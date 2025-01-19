@extends('layouts.app')

@section('title', '議論')

@section('content')
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">議論テーマ</h5>
    </div>
    <div class="card-body">
        <p class="card-text">ここにテーマが表示されます。</p>
    </div>
</div>
<div id="chat-area" class="border rounded p-3 mb-4" style="height: 300px; overflow-y: scroll;">
    <!-- チャット内容がここに表示される -->
</div>
<form id="chat-form" class="d-flex">
    <input type="text" id="user-input" class="form-control me-2" placeholder="メッセージを入力" required>
    <button type="submit" class="btn btn-success">送信</button>
</form>
@endsection
