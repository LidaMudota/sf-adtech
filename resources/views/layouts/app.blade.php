<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SF AdTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .kanban-column { min-height: 200px; }
        .offer-card { cursor: grab; }
    </style>
</head>
<body>
<noscript>
    <div class="alert alert-warning m-3">
        Для работы всех функций включите JavaScript. Базовые действия доступны и без него.
    </div>
</noscript>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">SF AdTech</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @auth
                    @if(auth()->user()->role === 'advertiser')
                        <li class="nav-item"><a class="nav-link" href="{{ route('offers.index') }}">Мои офферы</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('offers.kanban') }}">Доска</a></li>
                    @endif
                    @if(auth()->user()->role === 'webmaster')
                        <li class="nav-item"><a class="nav-link" href="{{ route('webmaster.subscriptions') }}">Подписки</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('webmaster.offers') }}">Офферы</a></li>
                    @endif
                    @if(auth()->user()->role === 'admin')
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.users') }}">Пользователи</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.stats') }}">Статистика</a></li>
                    @endif
                @endauth
            </ul>
            <ul class="navbar-nav ms-auto">
                @auth
                    <li class="nav-item"><span class="navbar-text text-white me-2">{{ auth()->user()->name }}</span></li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline-light btn-sm">Выход</button>
                        </form>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
<div class="container mb-5">
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="alert alert-danger d-none" data-error-box></div>
    <div class="alert alert-success d-none" data-flash-box></div>

    @yield('content')
</div>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
</script>
<script src="{{ asset('js/offer-sync.js') }}" defer></script>
@yield('scripts')
</body>
</html>
