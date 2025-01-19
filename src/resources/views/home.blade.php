@extends('layouts.app')

@section('title', 'ホーム')

@section('content')
<div class="text-center">
    <div class="mb-4">
        <p class="lead">このアプリでは、AIと議論を行うことができます。議論のテーマを決めて、AIとの対話を通じて新しい視点を見つけましょう。</p>
        <p class="lead">「議論する」ボタンを押して、さっそく始めましょう！</p>
    </div>
    <a href="{{ url('/debate') }}" class="btn btn-success btn-lg">議論する</a>
</div>
@endsection
