<?php

namespace Tests\Feature\App\Actions;

use App\Actions\DTOs\UserUpdateDTO;
use App\Actions\UserUpdateAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserUpdateActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Экшен обновляет данные пользователя.
     */
    public function test_updates_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $dto = UserUpdateDTO::make(
            name: 'Updated Name',
            email: 'updated@example.com',
        );

        (new UserUpdateAction())($user, $dto);

        $user->refresh();

        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.com', $user->email);
    }

    /**
     * При изменении email поле email_verified_at сбрасывается в null.
     */
    public function test_resets_email_verified_at_when_email_changes(): void
    {
        $user = User::factory()->create([
            'email' => 'original@example.com',
            'email_verified_at' => now(),
        ]);

        $dto = UserUpdateDTO::make(
            name: $user->name,
            email: 'new@example.com',
        );

        (new UserUpdateAction())($user, $dto);

        $user->refresh();

        $this->assertNull($user->email_verified_at);
    }

    /**
     * Если email не меняется, email_verified_at остаётся без изменений.
     */
    public function test_does_not_change_email_verified_at_when_email_unchanged(): void
    {
        $originalVerifiedAt = now();

        $user = User::factory()->create([
            'email' => 'original@example.com',
            'email_verified_at' => $originalVerifiedAt,
        ]);

        $dto = UserUpdateDTO::make(
            name: 'Updated Name',
            email: 'original@example.com',
        );

        (new UserUpdateAction())($user, $dto);

        $user->refresh();

        $this->assertEquals(
            $originalVerifiedAt->format('Y-m-d H:i:s'),
            $user->email_verified_at->format('Y-m-d H:i:s')
        );
    }
}
