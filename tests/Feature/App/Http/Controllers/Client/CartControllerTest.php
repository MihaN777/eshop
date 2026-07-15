<?php

namespace Tests\Feature\App\Http\Controllers\Client;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Traits\CreatesOrderData;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use CreatesOrderData;
    use RefreshDatabase;

    private const string OWN_CART = 'own-cart';

    private const string VICTIM_CART = 'victim-cart';

    /**
     * IDOR: нельзя изменить количество чужой позиции по её id.
     */
    public function test_quantity_cannot_modify_in_another_cart(): void
    {
        $this->bindFixedCartStorage(self::OWN_CART);
        $product = Product::factory()->create(['quantity' => 10]);
        $victimItem = $this->seedCartItem($product, 2, self::VICTIM_CART);

        $this->patch(route('cart.quantity', $victimItem), ['quantity' => 99])
            ->assertNotFound();

        $this->assertDatabaseHas('cart_items', [
            'id' => $victimItem->id,
            'quantity' => 2,
        ]);
    }

    /**
     * IDOR: нельзя удалить чужую позицию по её id.
     */
    public function test_cannot_delete_cart_item_in_another_cart(): void
    {
        $this->bindFixedCartStorage(self::OWN_CART);
        $product = Product::factory()->create(['quantity' => 10]);
        $victimItem = $this->seedCartItem($product, 2, self::VICTIM_CART);

        $this->delete(route('cart.delete', $victimItem))
            ->assertNotFound();

        $this->assertModelExists($victimItem);
    }

    /**
     * Happy path: количество своей позиции изменяется.
     */
    public function test_quantity_updates_own_cart_item(): void
    {
        $this->bindFixedCartStorage(self::OWN_CART);
        $product = Product::factory()->create(['quantity' => 10]);
        $ownItem = $this->seedCartItem($product, 1, self::OWN_CART);

        $this->patch(route('cart.quantity', $ownItem), ['quantity' => 4])
            ->assertRedirect(route('cart'));

        $this->assertDatabaseHas('cart_items', [
            'id' => $ownItem->id,
            'quantity' => 4,
        ]);
    }

    /**
     * Happy path: своя позиция удаляется.
     */
    public function test_delete_removes_own_cart_item(): void
    {
        $this->bindFixedCartStorage(self::OWN_CART);
        $product = Product::factory()->create(['quantity' => 10]);
        $ownItem = $this->seedCartItem($product, 1, self::OWN_CART);

        $this->delete(route('cart.delete', $ownItem))
            ->assertRedirect(route('cart'));

        $this->assertModelMissing($ownItem);
    }
}
