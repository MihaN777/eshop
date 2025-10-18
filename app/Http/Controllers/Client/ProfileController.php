<?php

namespace App\Http\Controllers\Client;

use App\Actions\DTOs\UserUpdateDTO;
use App\Actions\UserDeleteAction;
use App\Actions\UserUpdateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\ProfileDeleteRequest;
use App\Http\Requests\Client\ProfileUpdateRequest;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    public function profile(): View
    {
        return view('client.profile', ['authUser' => auth()->user()]);
    }

    public function update(ProfileUpdateRequest $request, UserUpdateAction $action): RedirectResponse
    {
        if(!$action(auth()->user(), UserUpdateDTO::fromRequest($request))) throw new DomainException('Не удалось обновить профиль пользователя');;
        flash()->info('Профиль обновлен');

        return redirect()->route('profile');
    }

    public function delete(ProfileDeleteRequest $request, UserDeleteAction $action): RedirectResponse
    {
        if(!$action(auth()->user())) throw new DomainException('Не удалось удалить профиль пользователя');
        flash()->info('Ваш профиль был успешно удален');

        return redirect()->route('home');
    }
}
