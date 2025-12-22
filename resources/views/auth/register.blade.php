@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Регистрация</div>
            <div class="card-body">
                <form method="POST" action="{{ route('register') }}" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Имя</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Повтор пароля</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Роль</label>
                        <select name="role" class="form-select" required>
                            <option value="advertiser" @selected(old('role')==='advertiser')>Рекламодатель</option>
                            <option value="webmaster" @selected(old('role')==='webmaster')>Веб-мастер</option>
                        </select>
                    </div>
                    <button class="btn btn-primary">Зарегистрироваться</button>
                    <a href="{{ route('login') }}" class="btn btn-link">Уже есть аккаунт</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
