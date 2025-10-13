@extends('layouts.client-layout')

@section('title', 'Профиль')

@section('content')
    <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        @method('PATCH')

        <div><input type="text" placeholder="Имя" style="color: black; margin-bottom:10px;" name="name" value="{{ old('name') ?? $authUser->name }}"></div>
        @error('name')
        <div style="color:red; padding:5px;">{{ $message }}</div>
        @enderror

        <div><input type="email" placeholder="Email" style="color: black; margin-bottom:10px;" name="email" value="{{ old('email') ?? $authUser->email }}"></div>
        @error('email')
        <div style="color:red; padding:5px;">{{ $message }}</div>
        @enderror

        <button style="border:#2563eb solid 1px; padding:5px;" type="submit">Обновить</button>
    </form>
@endsection
