<?php

namespace App\Http\Controllers\AuthCustom;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Exceptions\ProjectException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    private array $drivers;

    public function __construct()
    {
        $this->drivers = config('social_auth.drivers', []);
    }

    public function redirect(string $driver): \Symfony\Component\HttpFoundation\RedirectResponse|RedirectResponse
    {
        $this->isDriverSupported($driver);

        try {
            return Socialite::driver($driver)->redirect();
        } catch (Throwable $e) {
            throw new ProjectException(
                'Произошла ошибка перенаправления на страницу авторизации',
                "[LINE {$e->getLine()}] {$e->getFile()} >>> {$e->getMessage()}"
            );
        }
    }

    public function callback(string $driver): RedirectResponse
    {
        $this->isDriverSupported($driver);

        try {
            $socialUser = Socialite::driver($driver)->user();

            // updateOrCreate для обновления данных при каждом заходе через соц. сеть
            $user = User::query()->firstOrCreate([
                $this->drivers[$driver] => $socialUser->id,
            ], [
                'name' => $socialUser->name ?? $socialUser->nickname,
                'email' => $socialUser->email,
                'password' => Hash::make(Str::random(20)),
                'email_verified_at' => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            throw new ProjectException(
                'Произошла ошибка авторизации через социальную сеть',
                "[LINE {$e->getLine()}] {$e->getFile()} >>> {$e->getMessage()}"
            );
        }

        auth()->login($user);

        return redirect()->intended(route('home'));
    }

    private function isDriverSupported(string $driver): void
    {
        if (!array_key_exists($driver, $this->drivers)) throw new ProjectException('Драйвер социальной сети не поддерживатеся');
    }
}
