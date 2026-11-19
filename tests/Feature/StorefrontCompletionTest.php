<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class StorefrontCompletionTest extends TestCase
{
    use DatabaseTransactions;

    private function makePost(array $attrs = []): BlogPost
    {
        $title = $attrs['title'] ?? 'Post '.Str::random(6);

        return BlogPost::create(array_merge([
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
            'excerpt' => 'An editorial excerpt.',
            'content' => 'Editorial body copy.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ], $attrs));
    }

    private function makeOrder(User $user, string $status, int $qty = 2): Order
    {
        $order = Order::create([
            'order_number' => 'TST-'.Str::upper(Str::random(8)),
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'shipping_address' => ['address_line_1' => '1 Test Road', 'city' => 'Dhaka'],
            'subtotal' => 100 * $qty,
            'total' => 100 * $qty,
            'order_status' => $status,
        ]);

        $order->items()->create([
            'product_name' => 'Test Blazer',
            'price' => 100,
            'quantity' => $qty,
            'subtotal' => 100 * $qty,
        ]);

        return $order->load('items');
    }

    private function customer(): User
    {
        return User::where('email', 'customer@cloth.test')->firstOrFail();
    }

    // ---------------------------------------------------------------- Blog

    public function test_public_blog_lists_only_visible_posts(): void
    {
        $live = $this->makePost(['title' => 'Live Story']);
        $draft = $this->makePost(['title' => 'Draft Story', 'status' => 'draft']);
        $future = $this->makePost(['title' => 'Future Story', 'status' => 'scheduled', 'published_at' => now()->addWeek()]);
        $due = $this->makePost(['title' => 'Due Story', 'status' => 'scheduled', 'published_at' => now()->subHour()]);

        $slugs = collect($this->getJson('/api/v1/public/blog?per_page=30')
            ->assertOk()
            ->json('data.posts.data'))->pluck('slug');

        $this->assertContains($live->slug, $slugs);
        $this->assertContains($due->slug, $slugs);
        $this->assertNotContains($draft->slug, $slugs);
        $this->assertNotContains($future->slug, $slugs);
    }

    public function test_public_blog_post_detail_counts_views_and_hides_drafts(): void
    {
        $post = $this->makePost();
        $draft = $this->makePost(['status' => 'draft']);

        $this->getJson("/api/v1/public/blog/{$post->slug}")
            ->assertOk()
            ->assertJsonPath('data.post.slug', $post->slug);

        $this->assertSame(1, $post->fresh()->views_count);
        $this->getJson("/api/v1/public/blog/{$draft->slug}")->assertNotFound();
    }

    // ------------------------------------------------------------- Returns

    public function test_customer_can_request_return_for_delivered_order(): void
    {
        $customer = $this->customer();
        $order = $this->makeOrder($customer, 'delivered');
        $item = $order->items->first();

        $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/customer/orders/{$order->order_number}/returns", [
                'type' => 'return',
                'reason' => 'Size too large',
                'condition' => 'size_issue',
                'items' => [['order_item_id' => $item->id, 'quantity' => 1]],
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.refund_amount', '100.00');

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v1/customer/returns')
            ->assertOk()
            ->assertJsonPath('data.0.order.order_number', $order->order_number);
    }

    public function test_return_rejected_for_undelivered_order(): void
    {
        $customer = $this->customer();
        $order = $this->makeOrder($customer, 'pending');

        $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/customer/orders/{$order->order_number}/returns", [
                'type' => 'return',
                'reason' => 'Changed my mind',
                'items' => [['order_item_id' => $order->items->first()->id, 'quantity' => 1]],
            ])
            ->assertUnprocessable();
    }

    public function test_return_quantity_cannot_exceed_remaining_units(): void
    {
        $customer = $this->customer();
        $order = $this->makeOrder($customer, 'delivered', qty: 2);
        $itemId = $order->items->first()->id;
        $url = "/api/v1/customer/orders/{$order->order_number}/returns";
        $payload = fn (int $q) => ['type' => 'return', 'reason' => 'Defect', 'items' => [['order_item_id' => $itemId, 'quantity' => $q]]];

        $this->actingAs($customer, 'sanctum')->postJson($url, $payload(2))->assertCreated();
        $this->actingAs($customer, 'sanctum')->postJson($url, $payload(1))->assertUnprocessable();
    }

    public function test_customer_cannot_return_someone_elses_order(): void
    {
        $other = User::create([
            'name' => 'Other Customer',
            'email' => 'other-'.uniqid().'@example.test',
            'password' => 'secret123',
            'role' => 'customer',
            'is_active' => true,
        ]);
        $order = $this->makeOrder($other, 'delivered');

        $this->actingAs($this->customer(), 'sanctum')
            ->postJson("/api/v1/customer/orders/{$order->order_number}/returns", [
                'type' => 'return',
                'reason' => 'Not mine',
                'items' => [['order_item_id' => $order->items->first()->id, 'quantity' => 1]],
            ])
            ->assertNotFound();
    }

    public function test_return_rejects_item_from_another_order(): void
    {
        $customer = $this->customer();
        $order = $this->makeOrder($customer, 'delivered');
        $otherOrder = $this->makeOrder($customer, 'delivered');

        $this->actingAs($customer, 'sanctum')
            ->postJson("/api/v1/customer/orders/{$order->order_number}/returns", [
                'type' => 'exchange',
                'reason' => 'Wrong colour',
                'items' => [['order_item_id' => $otherOrder->items->first()->id, 'quantity' => 1]],
            ])
            ->assertUnprocessable();
    }
}
