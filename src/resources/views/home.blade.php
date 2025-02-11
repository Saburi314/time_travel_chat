@extends('layouts.app')

@section('title', 'ホーム')

@section('content')
<div class="text-center">
    <div class="mb-4">
        <p class="lead">このアプリでは、AIとディベートを行うことができます。</p>
        <p class="lead">議論相手を選んで、さっそく始めましょう！</p>
    </div>

    <!-- 議論相手の選択 -->
    <form id="opponent-form" action="{{ url('/debate') }}" method="GET">
        @foreach ($opponents as $key => $opponent)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="opponentKey" id="opponent-{{ $key }}" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }}>
                <label class="form-check-label" for="opponent-{{ $key }}">
                    {{ $opponent['name'] }}
                </label>
            </div>
        @endforeach

        <button type="submit" class="btn btn-success btn-lg mt-3">議論する</button>
    </form>
</div>
@endsection
