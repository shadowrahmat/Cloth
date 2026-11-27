<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class GuestSessionTest extends TestCase
{
    use DatabaseTransactions;

    private function product(): Product
    {
        return Product::create(['name' => 'Guest Test '.uniqid(), 'price' => 100, 'stock' => 50, 'status' => 'published']);
    }

    private function add(string $session, Product $product, int $qty = 1): void
    {
        $this->withHeader('X-Session-ID', $session)
            ->postJson('/api/v1/public/cart/add', ['product_id' => $product->id, 'quantity' => $qty])
            ->assertSuccessful();
    }

    private function customer(): User
    {
        return User::create([
            'name' => 'C', 'email' => 'c-'.uniqid().'@example.test', 'password' => 'secret123',
            'role' => 'customer', 'is_active' => true,
        ]);
    }

    public function test_a_session_id_that_is_not_a_uuid_is_refused(): void
    {
        foreach (['sess_abc123', '1', 'x'] as $bad) {
            $this->withHeader('X-Session-ID', $bad)->getJson('/api/v1/public/cart')->assertStatus(422);
            $this->withHeader('X-Session-ID', $bad)->getJson('/api/v1/public/wishlist')->assertStatus(422);
        }
    }

    public function test_a_uuid_session_id_works(): void
    {
        $this->withHeader('X-Session-ID', (string) Str::uuid())->getJson('/api/v1/public/cart')->assertOk();
    }

    public function test_a_cart_that_belongs_to_a_user_cannot_be_taken_through_its_session_id(): void
    {
        $victim = $this->customer();
        $sid = (string) Str::uuid();
        $product = $this->product();
        Cart::create(['user_id' => $victim->id, 'session_id' => $sid])->items()->create([
            'product_id' => $product->id, 'quantity' => 1, 'price' => 100,
        ]);

        $attacker = $this->customer();
        $this->withHeader('X-Session-ID', $sid)->postJson('/api/v1/customer/login', [
            'email' => $attacker->email, 'password' => 'secret123',
        ])->assertOk();

        $this->assertSame($victim->id, Cart::where('session_id', $sid)->first()->user_id);
        $this->assertNull(Cart::where('user_id', $attacker->id)->first());
    }

    public function test_login_merges_the_guest_cart_into_the_cart_the_user_already_has(): void
    {
        $user = $this->customer();
        $a = $this->product();
        $b = $this->product();

        $own = Cart::create(['user_id' => $user->id, 'session_id' => (string) Str::uuid()]);
        $own->items()->create(['product_id' => $a->id, 'quantity' => 1, 'price' => 100]);

        $sid = (string) Str::uuid();
        $this->add($sid, $a, 2);
        $this->add($sid, $b, 1);

        $this->withHeader('X-Session-ID', $sid)->postJson('/api/v1/customer/login', [
            'email' => $user->email, 'password' => 'secret123',
        ])->assertOk();

        $this->assertSame(1, Cart::where('user_id', $user->id)->count());
        $this->assertNull(Cart::where('session_id', $sid)->first());
        $items = $own->fresh()->items->pluck('quantity', 'product_id');
        $this->assertSame(3, $items[$a->id]);
        $this->assertSame(1, $items[$b->id]);
    }
}
