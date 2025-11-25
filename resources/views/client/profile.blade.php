@extends('layouts.client-layout')

@section('title', 'Профиль')

@section('content')
    <section>
        <div class="max-w-[640px] mt-12 mx-auto p-6 xs:p-8 md:p-12 2xl:p-16 rounded-[20px] bg-purple">
            <h1 class="mb-5 text-lg lg:text-[42px] font-black text-center">Редактировать профиль</h1>

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-3">
                @csrf
                @method('PATCH')

                <input type="text" name="name"
                       class="w-full h-14 px-4 rounded-lg border border-[#A07BF0] bg-white/20 focus:border-pink focus:shadow-[0_0_0_2px_#EC4176] outline-none transition text-white placeholder:text-white text-xxs md:text-xs font-semibold"
                       value="{{ old('name') ?? $authUser->name }}" placeholder="Имя">

                @error('name')
                <div style="color:red; padding:5px;">{{ $message }}</div>
                @enderror

                <input type="email" name="email"
                       class="w-full h-14 px-4 rounded-lg border border-[#A07BF0] bg-white/20 focus:border-pink focus:shadow-[0_0_0_2px_#EC4176] outline-none transition text-white placeholder:text-white text-xxs md:text-xs font-semibold"
                       value="{{ old('email') ?? $authUser->email }}" placeholder="E-mail">

                @error('email')
                <div style="color:red; padding:5px;">{{ $message }}</div>
                @enderror

                <button type="submit" class="w-full btn btn-pink">Сохранить</button>
            </form>

            <h1 class="mt-5 mb-5 text-lg lg:text-[42px] font-black text-center">Удалить профиль</h1>

            <form action="{{ route('profile.delete') }}" method="POST" class="space-y-3">
                @csrf
                @method('DELETE')

                @if(!$authUser->isSocialRegistered())
                    <input type="password" name="password"
                           class="w-full h-14 px-4 rounded-lg border border-[#A07BF0] bg-white/20 focus:border-pink focus:shadow-[0_0_0_2px_#EC4176] outline-none transition text-white placeholder:text-white text-xxs md:text-xs font-semibold"
                           placeholder="Текущий пароль" autocomplete="off" required>

                    @error('password')
                    <div style="color:red; padding:5px;">{{ $message }}</div>
                    @enderror
                @endif

                <button type="submit" class="w-full btn btn-pink">Удалить</button>
            </form>
        </div>
    </section>
@endsection
