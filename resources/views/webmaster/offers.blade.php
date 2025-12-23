@extends('layouts.app')
@section('content')
<h4 class="mb-3">Доступные офферы</h4>
<table class="table table-bordered align-middle js-offers-table" data-available-offers>
    <thead><tr><th>Имя</th><th>CPC рекламодателя</th><th>Статус</th><th>Темы</th><th>Подписка</th></tr></thead>
    <tbody>
    @foreach($offers as $offer)
        <tr data-offer-id="{{ $offer->id }}">
            <td>{{ $offer->name }}</td>
            <td>{{ $offer->price_per_click }}</td>
            <td>{{ $offer->status }}</td>
            <td>{{ $offer->topics->pluck('name')->join(', ') }}</td>
            <td>
                <form method="POST" action="{{ route('webmaster.subscribe', $offer) }}" class="subscribe-form d-flex gap-2" data-async="true" data-action="subscription-subscribe">
                    @csrf
                    <input type="number" step="0.01" name="webmaster_cpc" class="form-control form-control-sm" placeholder="Моя ставка" required>
                    <button class="btn btn-sm btn-primary">Подписаться</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection
