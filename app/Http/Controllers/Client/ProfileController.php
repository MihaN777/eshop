<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\Profile\DeleteAction;
use App\Actions\Client\Profile\UpdateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\ProfileDeleteRequest;
use App\Http\Requests\Client\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class ProfileController extends Controller
{
    private Authenticatable $authUser;

    public function __construct()
    {
        try {
            $this->authUser = auth()->user();
        } catch (Throwable $e) {
            logger()
                ->channel('telegram')
                ->error("[LINE {$e->getLine()}] {$e->getFile()} >>> {$e->getMessage()}");

            abort(500);
        }
    }

    public function profile(): View
    {
        return view('client.profile', ['authUser' => $this->authUser]);
    }

    public function update(ProfileUpdateRequest $request, UpdateAction $action): RedirectResponse
    {
        $action($request, $this->authUser);
        flash()->info('Профиль обновлен');

        return redirect()->route('profile');
    }

    public function delete(ProfileDeleteRequest $request, DeleteAction $action): RedirectResponse
    {
        $action($this->authUser);
        flash()->info('Ваш профиль был успешно удален');

        return redirect()->route('home');
    }
}
