@extends('layouts.app')

@section('title', 'ホーム')

@section('content')
<div class="text-center">
    <div class="mb-4">
        <p class="lead">このアプリでは、歴史上の人物と対話を行うことができます。</p>
        <p class="lead">偉人を選んで、さっそく始めましょう！</p>
    </div>

    <!-- 議論相手の選択 -->
    <form id="opponent-form" action="{{ url('/debate') }}" method="GET" class="d-flex flex-wrap justify-content-center">
        @foreach ($opponents as $opponent)
            <div class="card m-2" style="width: 18rem;" data-id="{{ $opponent->id }}">
                <img src="{{ asset($opponent->image) }}" class="card-img-top" alt="{{ $opponent->name }}">
                <div class="card-body">
                    <h5 class="card-title">{{ $opponent->name }}</h5>
                    <p class="card-text">{{ $opponent->system_message }}</p>
                    <input class="form-check-input d-none" type="radio" name="opponent_id" 
                        id="opponent-{{ $opponent->id }}" value="{{ $opponent->id }}" {{ $loop->first ? 'checked' : '' }}>
                </div>
            </div>
        @endforeach
    </form>
</div>

<!-- 🔹 JavaScript に値を渡す -->
<script>
    window.userToken = @json($userToken);

    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.card');
        const form = document.getElementById('opponent-form');
        cards.forEach(card => {
            card.addEventListener('click', function() {
                cards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                const radioInput = this.querySelector('input[type="radio"]');
                if (radioInput) {
                    radioInput.checked = true;
                    form.submit(); // フォームを自動送信
                }
            });
        });
    });
</script>

@endsection
