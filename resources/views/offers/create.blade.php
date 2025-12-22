@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-header">Новый оффер</div>
    <div class="card-body">
        <form method="POST" action="{{ route('offers.store') }}" novalidate>
            @csrf
            <div class="mb-3">
                <label class="form-label">Имя</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Цена за клик</label>
                <input type="number" step="0.01" name="price_per_click" value="{{ old('price_per_click') }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Целевой URL</label>
                <input type="url" name="target_url" value="{{ old('target_url') }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Статус</label>
                <select name="status" class="form-select">
                    <option value="draft">draft</option>
                    <option value="active">active</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Темы</label>
                <select name="topics[]" class="form-select" multiple>
                    @foreach($topics as $topic)
                        <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary">Создать</button>
        </form>
    </div>
</div>
@endsection
