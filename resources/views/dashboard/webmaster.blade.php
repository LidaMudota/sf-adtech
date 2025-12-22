@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Веб-мастер</h5>
        <p>Подписок: {{ $subscriptionsCount }}</p>
        <a href="{{ route('webmaster.offers') }}" class="btn btn-primary">Найти офферы</a>
    </div>
</div>
@endsection
