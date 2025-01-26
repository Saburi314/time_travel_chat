@extends('layouts.app')

@section('title', 'ホーム')

@section('content')
<div class="text-center">
    <div class="mb-4">

    <!-- TODO ここにアプリの説明を記載する -->
        <p class="lead">このアプリでは、AIとディベートを行うことができます。ディベートのテーマを決めて、AIとの対話を通じて新しい視点を見つけましょう。</p>
        <p class="lead">ディベートする」ボタンを押して、さっそく始めましょう！</p>
    </div>
    <a href="{{ url('/debate') }}" class="btn btn-success btn-lg">議論する</a>
</div>
@endsection
