@extends('layouts.app')
@section('content')
<h4 class="mb-3">Доска офферов</h4>
<div class="row" id="kanban-board">
    @foreach(['draft' => 'Черновик', 'active' => 'Активные', 'inactive' => 'Неактивные'] as $status => $label)
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">{{ $label }}</div>
                <div class="card-body kanban-column" data-status="{{ $status }}">
                    @foreach($offers[$status] ?? [] as $offer)
                        <div class="card mb-2 offer-card" draggable="true" data-offer="{{ $offer->id }}">
                            <div class="card-body p-2">
                                <strong>{{ $offer->name }}</strong>
                                <div class="small text-muted">CPC {{ $offer->price_per_click }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
<p class="mt-3 text-muted">Перетаскивайте карточки между колонками. Без JS статусы можно менять в списке офферов.</p>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.offer-card').forEach(card => {
        card.addEventListener('dragstart', event => {
            event.dataTransfer.setData('offer', card.dataset.offer);
        });
    });

    document.querySelectorAll('.kanban-column').forEach(column => {
        column.addEventListener('dragover', event => event.preventDefault());
        column.addEventListener('drop', event => {
            event.preventDefault();
            const offerId = event.dataTransfer.getData('offer');
            const status = column.dataset.status;
            fetch(`/offers/${offerId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({status})
            }).then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка сохранения статуса');
                }
                column.appendChild(document.querySelector(`[data-offer="${offerId}"]`));
            }).catch(() => alert('Не удалось обновить статус'));
        });
    });
</script>
@endsection
