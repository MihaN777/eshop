<?php

namespace Tests\Feature\App\Models;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartPrunableTest extends TestCase
{
    use RefreshDatabase;

    private function ageCart(Cart $cart, int $days): void
    {
        Cart::query()->whereKey($cart->getKey())->update([
            'created_at' => now()->subDays($days),
            'updated_at' => now()->subDays($days),
        ]);
    }

    /**
     * Гостевая корзина без активности больше суток вычищается.
     */
    public function test_prune_removes_old_guest_cart(): void
    {
        $guestCart = Cart::query()->create(['storage_id' => 'old-guest', 'user_id' => null]);
        $this->ageCart($guestCart, 2);

        $this->artisan('model:prune', ['--model' => [Cart::class]]);

        $this->assertModelMissing($guestCart);
    }

    /**
     * Корзина аккаунта не вычищается — она должна пережить логаут и вернуться при входе.
     */
    public function test_prune_keeps_cart_of_account(): void
    {
        $user = User::factory()->create();
        $userCart = Cart::query()->create(['storage_id' => null, 'user_id' => $user->getKey()]);
        $this->ageCart($userCart, 30);

        $this->artisan('model:prune', ['--model' => [Cart::class]]);

        $this->assertModelExists($userCart);
    }

    /**
     * Свежая гостевая корзина не трогается.
     */
    public function test_prune_keeps_fresh_guest_cart(): void
    {
        $guestCart = Cart::query()->create(['storage_id' => 'fresh-guest', 'user_id' => null]);

        $this->artisan('model:prune', ['--model' => [Cart::class]]);

        $this->assertModelExists($guestCart);
    }

    /**
     * Старая, но используемая гостевая корзина остаётся: считаем updated_at, а не created_at.
     */
    public function test_prune_keeps_old_but_recently_active_guest_cart(): void
    {
        $guestCart = Cart::query()->create(['storage_id' => 'active-guest', 'user_id' => null]);

        Cart::query()->whereKey($guestCart->getKey())->update([
            'created_at' => now()->subDays(5),
            'updated_at' => now(),
        ]);

        $this->artisan('model:prune', ['--model' => [Cart::class]]);

        $this->assertModelExists($guestCart);
    }
}
