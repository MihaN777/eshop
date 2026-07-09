<?php

namespace Tests\Feature\App\Actions;

use App\Actions\DTOs\UserRegisterDTO;
use App\Actions\UserRegisterAction;
use App\Events\SessionRegenerated;
use App\Mail\UserPasswordMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserRegisterActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // request()->session()
        // Инициализируем сессию для глобального request()
        $session = $this->app['session.store'];
        $session->start();

        // Привязываем сессию к текущему запросу
        $request = $this->app['request'];
        $request->setLaravelSession($session);
    }

    private function makeDto(array $overrides = []): UserRegisterDTO
    {
        $data = array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'secret123',
            'verified_email' => false,
            'login_user' => false,
            'remember_user' => false,
        ], $overrides);

        return new UserRegisterDTO(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            verified_email: $data['verified_email'],
            login_user: $data['login_user'],
            remember_user: $data['remember_user'],
        );
    }

    /**
     * Экшен создаёт пользователя в базе данных с корректными полями name и email.
     */
    public function test_creates_user(): void
    {
        $dto = $this->makeDto();

        $user = (new UserRegisterAction())($dto);

        $this->assertDatabaseHas('users', [
            'email' => $dto->email,
            'name' => $dto->name,
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($dto->email, $user->email);
        $this->assertEquals($dto->name, $user->name);
        $this->assertNotNull($user->password);
    }

    /**
     * Пароль пользователя хешируется через bcrypt, а не сохраняется в открытом виде.
     */
    public function test_hashed_password_is_set(): void
    {
        $dto = $this->makeDto();

        $user = (new UserRegisterAction())($dto);

        $this->assertTrue(Hash::check($dto->password, $user->password));
    }

    /**
     * При незаверенной email-адресе генерируется событие Registered
     * (для отправки письма с подтверждением).
     */
    public function test_fires_registered_event_when_email_not_verified(): void
    {
        Event::fake();

        $dto = $this->makeDto(['verified_email' => false]);

        (new UserRegisterAction())($dto);

        Event::assertDispatched(Registered::class);
    }

    /**
     * При уже верифицированной email-адресе событие Registered не генерируется.
     */
    public function test_does_not_fire_registered_event_when_email_verified(): void
    {
        Event::fake();

        $dto = $this->makeDto(['verified_email' => true]);

        (new UserRegisterAction())($dto);

        Event::assertNotDispatched(Registered::class);
    }

    /**
     * Поле email_verified_at заполняется текущим временем,
     * если email уже верифицирован.
     */
    public function test_sets_email_verified_at_when_email_is_verified(): void
    {
        $dto = $this->makeDto(['verified_email' => true]);

        $user = (new UserRegisterAction())($dto);

        $this->assertNotNull($user->email_verified_at);
    }

    /**
     * Поле email_verified_at остаётся null,
     * если email не был верифицирован.
     */
    public function test_does_not_set_email_verified_at_when_email_not_verified(): void
    {
        $dto = $this->makeDto(['verified_email' => false]);

        $user = (new UserRegisterAction())($dto);

        $this->assertNull($user->email_verified_at);
    }

    /**
     * После регистрации пользователю отправляется письмо с учётными данными
     * (адрес и пароль) через очередь.
     */
    public function test_sends_password_email(): void
    {
        Mail::fake();

        $dto = $this->makeDto();

        (new UserRegisterAction())($dto);

        Mail::assertQueued(UserPasswordMail::class, function ($mail) use ($dto) {
            return $mail->hasTo($dto->email)
                && $mail->email === $dto->email
                && $mail->password === $dto->password;
        });
    }

    /**
     * Если login_user = true, пользователь автоматически авторизуется
     * и генерируется событие SessionRegenerated.
     */
    public function test_user_is_authenticated_and_session_regenerated_when_login_user_is_true(): void
    {
        Event::fake();

        $dto = $this->makeDto(['login_user' => true]);
        $user = (new UserRegisterAction())($dto);

        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(SessionRegenerated::class);
    }

    /**
     * Если login_user = false, пользователь не авторизуется
     * после регистрации.
     */
    public function test_does_not_login_user_when_login_user_is_false(): void
    {
        $dto = $this->makeDto(['login_user' => false]);

        $user = (new UserRegisterAction())($dto);

        $this->assertGuest();
    }

    /**
     * Флаг remember_user учитывается при авторизации —
     * пользователь остаётся в системе после закрытия браузера.
     */
    public function test_respects_remember_user_flag(): void
    {
        $dto = $this->makeDto([
            'login_user' => true,
            'remember_user' => true,
        ]);

        $user = (new UserRegisterAction())($dto);

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->remember_token);
    }

    /**
     * При отсутствии авторизации событие SessionRegenerated не генерируется.
     */
    public function test_does_not_fire_session_regenerated_event_when_not_logging_in(): void
    {
        Event::fake();

        $dto = $this->makeDto(['login_user' => false]);

        (new UserRegisterAction())($dto);

        Event::assertNotDispatched(SessionRegenerated::class);
    }
}
