@extends('layouts.app')
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <h5>{{ $offer->name }}</h5>
        <p>CPC рекламодателя: {{ $offer->price_per_click }}</p>
        <p>URL: {{ $offer->target_url }}</p>
        <p>Статус: {{ $offer->status }}</p>
        <p>Темы: {{ $offer->topics->pluck('name')->join(', ') }}</p>
    </div>
</div>
<div class="row">
    @foreach($stats as $period => $rows)
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header text-capitalize">{{ $period }}</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>Период</th><th>Клики</th><th>Редиректы</th><th>Расход</th><th>Доход ВМ</th><th>Система</th></tr></thead>
                        <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ $row['clicks'] }}</td>
                                <td>{{ $row['redirects'] }}</td>
                                <td>{{ $row['advertiser_cost'] }}</td>
                                <td>{{ $row['webmaster_income'] }}</td>
                                <td>{{ $row['system_income'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
