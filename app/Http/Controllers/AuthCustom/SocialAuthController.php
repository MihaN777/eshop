<?php

namespace App\Http\Controllers\AuthCustom;

use App\Http\Controllers\Controller;
use App\Models\User;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    public const SOCIAL_AUTH_DRIVERS = [
        'github' => 'github_id',
        // 'vk' => 'vk_id',
    ];

    public function redirect(string $driver): \Symfony\Component\HttpFoundation\RedirectResponse|RedirectResponse
    {
        if (!array_key_exists($driver, self::SOCIAL_AUTH_DRIVERS)) throw new DomainException('Драйвер социальной сети не поддерживатеся');

        try {
            return Socialite::driver($driver)->redirect();
        } catch (Throwable $e) {
            throw new DomainException('Произошла ошибка перенаправления на страницу авторизации');
        }
    }

    public function callback(string $driver): RedirectResponse
    {
        if (!array_key_exists($driver, self::SOCIAL_AUTH_DRIVERS)) throw new DomainException('Драйвер социальной сети не поддерживатеся');

        try {
            $socialUser = Socialite::driver($driver)->user();
        } catch (Throwable $e) {
            throw new DomainException('Произошла ошибка авторизации через социальную сеть');
        }

        $user = User::query()->updateOrCreate([
            self::SOCIAL_AUTH_DRIVERS[$driver] => $socialUser->id,
        ], [
            'name' => $socialUser->name ?? $socialUser->nickname,
            'email' => $socialUser->email,
            'password' => Hash::make(Str::random(15)),
            'email_verified_at' => now()->format('Y-m-d H:i:s'),
        ]);

        auth()->login($user);

        return redirect()->intended(route('home'));
    }
}
