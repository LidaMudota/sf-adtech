@extends('layouts.app')
@section('content')
<h4 class="mb-3">Пользователи</h4>
<table class="table table-bordered">
    <thead><tr><th>Имя</th><th>Email</th><th>Роль</th><th>Активен</th><th>Действия</th></tr></thead>
    <tbody>
    @foreach($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->role }}</td>
            <td>{{ $user->is_active ? 'Да' : 'Нет' }}</td>
            <td>
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="user-status d-flex gap-2 align-items-center">
                    @csrf
                    <select name="is_active" class="form-select form-select-sm">
                        <option value="1" @selected($user->is_active)>Активен</option>
                        <option value="0" @selected(!$user->is_active)>Заблокирован</option>
                    </select>
                    <button class="btn btn-sm btn-primary">Сохранить</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection
@section('scripts')
<script>
    document.querySelectorAll('.user-status').forEach(form => {
        form.addEventListener('submit', event => {
            if (!window.fetch) { return; }
            event.preventDefault();
            const data = new FormData(form);
            fetch(form.action, {method: 'POST', headers: {'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json'}, body: data})
                .then(resp => { if (!resp.ok) throw new Error(); alert('Обновлено'); })
                .catch(() => form.submit());
        });
    });
</script>
@endsection
