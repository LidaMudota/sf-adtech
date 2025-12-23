@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Мои офферы</h4>
    <a href="{{ route('offers.create') }}" class="btn btn-primary">Создать</a>
</div>
<div class="alert alert-info d-none" data-inline-flash></div>
<table class="table table-bordered align-middle" data-offers-table data-show-base="{{ url('/offers') }}" data-status-base="{{ url('/offers') }}" data-deactivate-base="{{ url('/offers') }}">
    <thead>
    <tr>
        <th>Имя</th>
        <th>CPC</th>
        <th>URL</th>
        <th>Статус</th>
        <th>Подписок</th>
        <th>Действия</th>
    </tr>
    </thead>
    <tbody data-offers-body>
    @foreach($offers as $offer)
        <tr data-entity="offer" data-id="{{ $offer->id }}">
            <td><a href="{{ route('offers.show', $offer) }}">{{ $offer->name }}</a></td>
            <td>{{ $offer->price_per_click }}</td>
            <td>{{ $offer->target_url }}</td>
            <td>
                <form method="POST" action="{{ route('offers.status', $offer) }}" class="d-flex gap-2 align-items-center" data-async="true" data-action="offer-status">
                    @csrf
                    <select name="status" class="form-select form-select-sm">
                        <option value="draft" @selected($offer->status==='draft')>draft</option>
                        <option value="active" @selected($offer->status==='active')>active</option>
                        <option value="inactive" @selected($offer->status==='inactive')>inactive</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary">Обновить</button>
                </form>
            </td>
            <td>{{ $offer->subscriptions_count }}</td>
            <td>
                <div class="d-flex gap-2">
                    <a href="{{ route('offers.show', $offer) }}" class="btn btn-sm btn-outline-secondary">Статистика</a>
                    <form method="POST" action="{{ route('offers.deactivate', $offer) }}" data-async="true" data-action="offer-deactivate" data-confirm="Деактивировать?">
                        @csrf
                        <button class="btn btn-sm btn-danger">Деактивировать</button>
                    </form>
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection
