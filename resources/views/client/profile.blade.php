@extends('layouts.client-layout')

@section('title', 'Профиль')

@section('content')
    <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        @method('PATCH')

        <div><input type="text" placeholder="Имя" style="color: black; margin-bottom:10px;" name="name"
                    value="{{ old('name') ?? $authUser->name }}"></div>
        @error('name')
        <div style="color:red; padding:5px;">{{ $message }}</div>
        @enderror

        <div><input type="email" placeholder="Email" style="color: black; margin-bottom:10px;" name="email"
                    value="{{ old('email') ?? $authUser->email }}"></div>
        @error('email')
        <div style="color:red; padding:5px;">{{ $message }}</div>
        @enderror

        <button style="border:#2563eb solid 1px; padding:5px;" type="submit">Обновить</button>
    </form>

    <form action="{{ route('profile.delete') }}" method="POST" style="margin-top:30px;">
        @csrf
        @method('DELETE')

        @if(!$authUser->isSocialRegistered())
            <div>
                <input type="password" name="password" placeholder="Текущий пароль" autocomplete="off"
                        style="color: black; margin-bottom:10px;">
            </div>
            @error('password')
            <div style="color:red; padding:5px;">{{ $message }}</div>
            @enderror
        @endif

        <button style="border:#eb2525 solid 1px; padding:5px;" type="submit">Удалить профиль</button>
    </form>
@endsection
