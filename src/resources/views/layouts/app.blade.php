<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ディベートアプリ RONPAI')</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('styles') <!-- 個別のCSS読み込み -->
</head>
<body class="bg-light">
    <header class="bg-success text-white py-3">
        <div class="container">
            <a href="{{ url('/') }}" class="text-white text-decoration-none">
                <h1 class="text-center">RONPAI -ディベートアプリ-</h1>
            </a>
        </div>
    </header>
    <main class="container py-4">
        @yield('content')
    </main>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/common.js') }}"></script>
    @yield('scripts') <!-- 個別のJS読み込み -->
</body>
</html>
