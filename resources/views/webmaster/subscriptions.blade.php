@extends('layouts.app')
@section('content')
<h4 class="mb-3">Мои подписки</h4>
<table class="table table-bordered align-middle" data-subscriptions-table>
    <thead>
    <tr>
        <th>Оффер</th>
        <th>Ставка</th>
        <th>Статус оффера</th>
        <th>Ссылка</th>
        <th>Действия</th>
    </tr>
    </thead>
    <tbody>
    @foreach($subs as $sub)
        <tr data-subscription-id="{{ $sub->id }}">
            <td>{{ $sub->offer->name }}</td>
            <td>{{ $sub->webmaster_cpc }}</td>
            <td>{{ $sub->offer->status }}</td>
            <td><code>{{ url('/r/' . $sub->token) }}</code></td>
            <td class="d-flex gap-2">
                <a href="{{ route('webmaster.stats', $sub) }}" class="btn btn-sm btn-outline-primary">Статистика</a>
                <form method="POST" action="{{ route('webmaster.unsubscribe', $sub) }}" data-async="true" data-action="subscription-unsubscribe">
                    @csrf
                    <button class="btn btn-sm btn-danger">Отписаться</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection
