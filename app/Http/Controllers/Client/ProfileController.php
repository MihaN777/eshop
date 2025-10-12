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
    private ?Authenticatable $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function profile(): View
    {
        return view('client.profile', ['user' => $this->user]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->user->fill($request->validated());

        if ($this->user->isDirty('email')) $this->user->email_verified_at = null;
        if (!$this->user->save()) throw new DomainException('Не удалось сохранить данные');

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
