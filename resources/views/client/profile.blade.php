@extends('layouts.client-layout')

@section('title', 'Профиль')

@section('content')
    <h1>Профиль пользователя: {{ $user->name }}</h1>
@endsection
