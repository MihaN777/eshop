<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\ProfileUpdateRequest;
use DomainException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ProfileController extends Controller
{
    private ?Authenticatable $authUser;

    public function __construct()
    {
        $this->authUser = auth()->user();
    }

    public function profile(): View
    {
        return view('client.profile', ['authUser' => $this->authUser]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->authUser->fill($request->validated());

        if ($this->authUser->isDirty('email')) $this->authUser->email_verified_at = null;
        if (!$this->authUser->save()) throw new DomainException('Не удалось сохранить данные');

        flash()->info('Профиль обновлен');
        return Redirect::route('profile');
    }

    public function delete(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
