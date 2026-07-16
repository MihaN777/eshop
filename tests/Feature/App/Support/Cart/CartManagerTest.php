<?php

namespace Tests\Feature\App\Support\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Option;
use App\Models\OptionValue;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Traits\CreatesOrderData;
use Tests\TestCase;

class CartManagerTest extends TestCase
{
    use CreatesOrderData;
    use RefreshDatabase;

    /**
     * Авторизованный с двух устройств работает с одной корзиной,
     * а не плодит вторую по новому storage_id.
     */
    public function test_authenticated_user_shares_one_cart_across_devices(): void
    {
        $user = User::factory()->create();
        $laptopProduct = Product::factory()->create(['quantity' => 10]);
        $phoneProduct = Product::factory()->create(['quantity' => 10]);

        $this->actingAs($user);

        $this->bindFixedCartStorage('laptop-session');
        cart()->add($laptopProduct, 1);

        $this->bindFixedCartStorage('phone-session');
        cart()->add($phoneProduct, 1);

        $this->assertSame(1, Cart::query()->where('user_id', $user->id)->count());
        $this->assertCount(2, cart()->items());
    }

    /**
     * Запись и чтение разрешаются в одну строку: добавленное на втором устройстве
     * сразу видно, а не проваливается в невидимую корзину.
     */
    public function test_added_product_is_visible_from_another_device(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['quantity' => 10]);

        $this->actingAs($user);

        $this->bindFixedCartStorage('phone-session');
        cart()->add($product, 3);

        $this->bindFixedCartStorage('laptop-session');

        $this->assertCount(1, cart()->items());
        $this->assertSame(3, cart()->items()->first()->quantity);
    }

    /**
     * Слияние на логине: пересекающаяся позиция суммируется, гостевая корзина исчезает.
     */
    public function test_login_merge_sums_quantities_of_matching_items(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['quantity' => 10]);

        $this->seedUserCartItem($product, 2, $user);
        $this->seedCartItem($product, 3, 'guest-session');

        $this->actingAs($user);
        $this->bindFixedCartStorage('after-login');
        cart()->handleSessionRegenerated('guest-session', 'after-login');

        $items = cart()->items();

        $this->assertCount(1, $items);
        $this->assertSame(5, $items->first()->quantity);
        $this->assertSame(1, Cart::query()->count());
    }

    /**
     * Слияние на логине: непересекающаяся позиция переезжает вместе с pivot опций.
     */
    public function test_login_merge_moves_items_and_keeps_option_pivot(): void
    {
        $user = User::factory()->create();
        $userProduct = Product::factory()->create(['quantity' => 10]);
        $guestProduct = Product::factory()->create(['quantity' => 10]);

        Option::factory()->create();
        $optionValue = OptionValue::factory()->create();

        $this->seedUserCartItem($userProduct, 1, $user);

        $this->bindFixedCartStorage('guest-session');
        cart()->add($guestProduct, 2, [$optionValue->id]);
        $guestItem = CartItem::query()->where('product_id', $guestProduct->id)->firstOrFail();

        $this->actingAs($user);
        $this->bindFixedCartStorage('after-login');
        cart()->handleSessionRegenerated('guest-session', 'after-login');

        $userCart = Cart::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertCount(2, cart()->items());
        $this->assertSame($userCart->id, $guestItem->fresh()->cart_id);
        $this->assertCount(1, $guestItem->fresh()->optionValues);
    }

    /**
     * Логин без собственной корзины: гостевая становится корзиной аккаунта.
     */
    public function test_login_without_existing_user_cart_adopts_guest_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['quantity' => 10]);

        $this->seedCartItem($product, 4, 'guest-session');

        $this->actingAs($user);
        $this->bindFixedCartStorage('after-login');
        cart()->handleSessionRegenerated('guest-session', 'after-login');

        $items = cart()->items();

        $this->assertCount(1, $items);
        $this->assertSame(4, $items->first()->quantity);
        $this->assertDatabaseHas('carts', ['user_id' => $user->id, 'storage_id' => null]);
    }

    /**
     * Логаут: корзина остаётся за аккаунтом, гостевая сессия стартует пустой.
     */
    public function test_logout_keeps_cart_with_account_and_leaves_guest_empty(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['quantity' => 10]);

        $this->actingAs($user);
        $this->bindFixedCartStorage('authed-session');
        cart()->add($product, 2);

        auth()->logout();
        $this->bindFixedCartStorage('guest-session');
        cart()->handleSessionRegenerated('authed-session', 'guest-session');

        $this->assertCount(0, cart()->items());
        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);

        $this->actingAs($user);
        $this->assertCount(1, cart()->items());
    }

    /**
     * Общий браузер: после выхода одного пользователя вошедший следующим
     * не получает его корзину.
     */
    public function test_next_user_on_shared_browser_does_not_inherit_previous_cart(): void
    {
        $previousUser = User::factory()->create();
        $nextUser = User::factory()->create();
        $product = Product::factory()->create(['quantity' => 10]);

        $this->actingAs($previousUser);
        $this->bindFixedCartStorage('session-a');
        cart()->add($product, 2);

        auth()->logout();
        $this->bindFixedCartStorage('session-b');
        cart()->handleSessionRegenerated('session-a', 'session-b');

        $this->actingAs($nextUser);
        $this->bindFixedCartStorage('session-c');
        cart()->handleSessionRegenerated('session-b', 'session-c');

        $this->assertCount(0, cart()->items());
        $this->assertDatabaseMissing('carts', ['user_id' => $nextUser->id]);
        $this->assertDatabaseHas('carts', ['user_id' => $previousUser->id]);
    }

    /**
     * Повторное добавление того же товара не плодит вторую позицию.
     */
    public function test_adding_same_product_twice_keeps_single_line(): void
    {
        Option::factory()->create();
        $optionValue = OptionValue::factory()->create();

        $product = Product::factory()->create(['quantity' => 10]);

        $this->bindFixedCartStorage('guest-session');
        cart()->add($product, 2, [$optionValue->id]);
        cart()->add($product, 3, [$optionValue->id]);

        $this->assertSame(1, CartItem::query()->count());
        $this->assertSame(5, cart()->items()->first()->quantity);
    }

    /**
     * Добавление товара метит корзину активной — на этом времени держится прунинг (очистка).
     */
    public function test_adding_product_marks_cart_as_active(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);

        $this->bindFixedCartStorage('guest-session');
        cart()->add($product, 1);

        $cart = Cart::query()->firstOrFail();
        Cart::query()->whereKey($cart->getKey())->update(['updated_at' => now()->subDays(5)]);

        cart()->add($product, 1);

        $this->assertTrue($cart->fresh()->updated_at->isAfter(now()->subMinute()));
    }

    /**
     * Ротация гостевой сессии без логина не теряет корзину.
     */
    public function test_guest_cart_survives_session_rotation(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);

        $this->bindFixedCartStorage('guest-old');
        cart()->add($product, 2);

        $this->bindFixedCartStorage('guest-new');
        cart()->handleSessionRegenerated('guest-old', 'guest-new');

        $this->assertCount(1, cart()->items());
        $this->assertDatabaseHas('carts', ['storage_id' => 'guest-new', 'user_id' => null]);
    }

    /**
     * unique(storage_id) не мешает нескольким пользовательским корзинам
     * существовать одновременно: NULL-ы в уникальном индексе БД различны.
     */
    public function test_user_carts_of_different_users_coexist(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $product = Product::factory()->create(['quantity' => 10]);

        $this->actingAs($firstUser);
        $this->bindFixedCartStorage('first-session');
        cart()->add($product, 1);

        $this->actingAs($secondUser);
        $this->bindFixedCartStorage('second-session');
        cart()->add($product, 2);

        $this->assertSame(2, Cart::query()->whereNull('storage_id')->count());
        $this->assertSame(2, cart()->items()->first()->quantity);

        $this->actingAs($firstUser);
        $this->assertSame(1, cart()->items()->first()->quantity);
    }
}
