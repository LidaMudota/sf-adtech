@extends('layouts.app')
@section('content')
<style>
  .table thead th { vertical-align: middle; white-space: nowrap; }
  .table td { white-space: nowrap; }
</style>

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

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th rowspan="2" class="align-middle">Период</th>
                                <th rowspan="2" class="align-middle text-center">Клики</th>
                                <th rowspan="2" class="align-middle text-center split-right">Редиректы</th>

                                <th colspan="1" class="text-center split-right">Расход</th>
                                <th colspan="2" class="text-center">Доход</th>
                            </tr>
                            <tr>
                                <th class="text-center split-right">РК</th>
                                <th class="text-center">ВМ</th>
                                <th class="text-center">Система</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td class="text-end">{{ $row['clicks'] }}</td>
                                    <td class="text-end split-right">{{ $row['redirects'] }}</td>

                                    <td class="text-end split-right">{{ $row['advertiser_cost'] }}</td>
                                    <td class="text-end">{{ $row['webmaster_income'] }}</td>
                                    <td class="text-end">{{ $row['system_income'] }}</td>
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
