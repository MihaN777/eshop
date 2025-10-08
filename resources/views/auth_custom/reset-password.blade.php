@extends('layouts.auth-layout')

@section('title', 'Востановление пароля')

@section('content')
    <x-forms.auth-forms title="Востановить пароль" action="{{ route('password.reset.send') }}" method="POST">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <x-forms.text-input
            name="email"
            type="email"
            placeholder="E-mail"
            required="required"
            value="{{ request('email') }}"
            :isError="$errors->has('email')"
        />

        @error('email')
        <x-forms.error>
            {{ $message }}
        </x-forms.error>
        @enderror

        <x-forms.text-input
            name="password"
            type="password"
            placeholder="Пароль"
            required="required"
            :isError="$errors->has('password')"
        />

        @error('password')
        <x-forms.error>
            {{ $message }}
        </x-forms.error>
        @enderror

        <x-forms.text-input
            name="password_confirmation"
            type="password"
            placeholder="Повторите пароль"
            required="required"
            :isError="$errors->has('password_confirmation')"
        />

        @error('password_confirmation')
        <x-forms.error>
            {{ $message }}
        </x-forms.error>
        @enderror

        <x-forms.primary-button>
            Обновить пароль
        </x-forms.primary-button>

        {{-- <x-slot:socialAuth></x-slot:socialAuth> --}}

        <x-slot:buttons></x-slot:buttons>
    </x-forms.auth-forms>
@endsection

