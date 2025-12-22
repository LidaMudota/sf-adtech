@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Рекламодатель</h5>
        <p>Офферов создано: {{ $offersCount }}</p>
        <a href="{{ route('offers.create') }}" class="btn btn-primary">Создать оффер</a>
    </div>
</div>
@endsection
