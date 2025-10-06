@extends('layouts.auth-layout')

@section('title', 'Подтверждение электронной почты')

@section('content')
    <h1>Подтвердите свой адрес электронной почты:</h1>

    <div class="mt-4">
        Прежде чем продолжить, пожалуйста, проверьте свой электронный адрес на наличие ссылки для подтверждения. Если
        вы не получили электронное письмо со ссылкой на подтверждение,
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" style="text-decoration: underline; cursor: pointer">
                нажите здесь, чтобы получить новую ссылку.
            </button>
        </form>
    </div>
@endsection

