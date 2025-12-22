@extends('layouts.app')
@section('content')
<h4 class="mb-3">Доступные офферы</h4>
<table class="table table-bordered align-middle js-offers-table">
    <thead><tr><th>Имя</th><th>CPC рекламодателя</th><th>Статус</th><th>Темы</th><th>Подписка</th></tr></thead>
    <tbody>
    @foreach($offers as $offer)
        <tr data-offer-id="{{ $offer->id }}">
            <td>{{ $offer->name }}</td>
            <td>{{ $offer->price_per_click }}</td>
            <td>{{ $offer->status }}</td>
            <td>{{ $offer->topics->pluck('name')->join(', ') }}</td>
            <td>
                <form method="POST" action="{{ route('webmaster.subscribe', $offer) }}" class="subscribe-form js-subscribe-form d-flex gap-2">
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
@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const offersTable = document.querySelector('.js-offers-table');
        if (!offersTable) { return; }

        offersTable.addEventListener('submit', event => {
            const form = event.target;
            if (!form.classList.contains('js-subscribe-form')) { return; }
            if (!window.fetch) { return; }

            event.preventDefault();
            const data = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: data
            })
                .then(async response => {
                    if (!response.ok) {
                        const errorData = await response.json().catch(() => null);
                        const message = errorData?.message ?? 'Ошибка при оформлении подписки.';
                        alert(message);
                        return null;
                    }

                    return response.json().catch(() => null);
                })
                .then(data => {
                    if (!data || data.status !== 'ok') { return; }
                    const offerRow = form.closest('[data-offer-id]');
                    if (offerRow) {
                        offerRow.remove();
                    }
                })
                .catch(() => {
                    form.submit();
                });
        });
    });
</script>
@endsection
