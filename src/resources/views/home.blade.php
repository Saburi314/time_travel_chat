@extends('layouts.app')

@section('title', 'ホーム')

@section('content')
<div class="text-center">
    <div class="mb-4">
        <p class="lead">このアプリでは、歴史上の人物と対話を行うことができます。</p>
        <p class="lead">偉人を選んで、さっそく始めましょう！</p>
    </div>

    <!-- 議論相手の選択 -->
    <form id="opponent-form" action="{{ url('/debate') }}" method="GET">
        @foreach ($opponents as $opponent)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="opponent_id" 
                    id="opponent-{{ $opponent->id }}" value="{{ $opponent->id }}" {{ $loop->first ? 'checked' : '' }}>
                <label class="form-check-label" for="opponent-{{ $opponent->id }}">
                    {{ $opponent->name }}
                </label>
            </div>
        @endforeach

        <button type="submit" class="btn btn-success btn-lg mt-3">会話する</button>
    </form>
</div>

<!-- 🔹 JavaScript に値を渡す -->
<script>
    window.userToken = @json($userToken);
</script>

@endsection
