@extends('layouts.app')
@section('content')
<h4 class="mb-3">Статистика</h4>
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card"><div class="card-body">Расход рекламодателей: {{ $income['advertiser'] }}</div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body">Доход веб-мастеров: {{ $income['webmaster'] }}</div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body">Доход системы: {{ $income['system'] }}</div></div>
    </div>
</div>
<div class="row">
    @foreach($clickStats as $period => $rows)
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header text-capitalize">{{ $period }}</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>Период</th><th>Успех</th><th>Отказы</th></tr></thead>
                        <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td>{{ $row->label }}</td>
                                <td>{{ $row->successful }}</td>
                                <td>{{ $row->failed }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
<div class="card">
    <div class="card-header">Выданные ссылки (последние)</div>
    <div class="card-body">
        <table class="table table-sm">
            <thead><tr><th>Оффер</th><th>Веб-мастер</th><th>Ссылка</th><th>Ставка</th></tr></thead>
            <tbody>
            @foreach($subscriptions as $sub)
                <tr>
                    <td>{{ $sub->offer->name }}</td>
                    <td>{{ $sub->webmaster->name }}</td>
                    <td>{{ url('/r/' . $sub->token) }}</td>
                    <td>{{ $sub->webmaster_cpc }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
