<?php

namespace App\Models;

use App\Notifications\AuthCustom\ResetPasswordNotification;
use App\Notifications\AuthCustom\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'github_id',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'github_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Отправить кастомных шаблонов писем через очередь

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    // Аксессоры и мутаторы

    public function avatar(): Attribute
    {
        return Attribute::make(
            get: fn() => "https://ui-avatars.com/api/?background=0D8ABC&color=fff&name={$this->name}",
        );
    }

    // Функции модели

    public function isSocialRegistered(): bool
    {
        foreach (config('social_auth.drivers', []) as $driver => $id) {
            if (isset($this->{$id})) return true;
        }

        return false;
    }
}
