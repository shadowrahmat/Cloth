<?php

namespace Tests\Feature;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailTemplatesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->firstOrFail();
    }

    private function placeOrder(string $email = 'buyer@example.com'): Order
    {
        $session = (string) Str::uuid();
        $variant = ProductVariant::where('stock', '>', 2)->firstOrFail();
        $this->withHeader('X-Session-ID', $session)
            ->postJson('/api/v1/public/cart/add', ['product_id' => $variant->product_id, 'variant_id' => $variant->id, 'quantity' => 1])
            ->assertOk();

        $res = $this->withHeader('X-Session-ID', $session)->postJson('/api/v1/public/checkout', [
            'first_name' => 'Rahim', 'last_name' => 'Uddin', 'email' => $email, 'phone' => '01700000000',
            'address_line_1' => 'House 1, Road 2', 'city' => 'Dhaka', 'state' => 'Dhaka', 'postal_code' => '1212',
            'country' => 'Bangladesh', 'payment_method' => 'cod',
        ])->assertStatus(201);

        return Order::findOrFail($res->json('data.order_id'));
    }

    public function test_admin_sees_all_store_templates_with_variables_and_status(): void
    {
        $res = $this->actingAs($this->admin(), 'sanctum')->getJson('/api/v1/admin/email-templates')->assertOk();

        $keys = array_column($res->json('data.templates'), 'key');
        $this->assertSame(['order_placed', 'order_confirmed', 'order_shipped', 'order_delivered', 'order_cancelled', 'admin_new_order'], $keys);
        $this->assertContains('order_number', array_column($res->json('data.variables'), 'key'));
        $this->assertFalse($res->json('data.status.ready'));
    }

    public function test_placing_an_order_emails_the_buyer_and_the_shop(): void
    {
        SiteSetting::set('mail_admin_emails', 'owner@example.com, staff@example.com', 'mail');

        $order = $this->placeOrder('buyer@example.com');

        $logs = EmailLog::where('order_id', $order->id)->get();
        $this->assertEqualsCanonicalizing(
            ['order_placed:buyer@example.com', 'admin_new_order:owner@example.com', 'admin_new_order:staff@example.com'],
            $logs->map(fn ($l) => "{$l->template_key}:{$l->to_email}")->all()
        );
        $this->assertStringContainsString($order->order_number, $logs->firstWhere('template_key', 'order_placed')->subject);
        $this->assertNotContains('failed', $logs->pluck('status')->all());
    }

    public function test_status_change_emails_the_customer_unless_switched_off(): void
    {
        $order = $this->placeOrder();
        $admin = $this->admin();

        $this->actingAs($admin, 'sanctum')->putJson("/api/v1/admin/orders/{$order->id}/status", ['order_status' => 'shipped', 'tracking_number' => 'SF-777'])->assertOk();
        $shipped = EmailLog::where('order_id', $order->id)->where('template_key', 'order_shipped')->firstOrFail();
        $this->assertSame($order->customer_email, $shipped->to_email);

        $this->actingAs($admin, 'sanctum')->putJson("/api/v1/admin/orders/{$order->id}/status", ['order_status' => 'delivered', 'notify_customer' => false])->assertOk();
        $this->assertFalse(EmailLog::where('order_id', $order->id)->where('template_key', 'order_delivered')->exists());
    }

    public function test_a_switched_off_template_is_not_sent(): void
    {
        $this->actingAs($this->admin(), 'sanctum')->getJson('/api/v1/admin/email-templates')->assertOk();
        EmailTemplate::where('key', 'order_placed')->update(['is_active' => false]);

        $order = $this->placeOrder();

        $this->assertFalse(EmailLog::where('order_id', $order->id)->where('template_key', 'order_placed')->exists());
    }

    public function test_admin_can_edit_preview_test_and_reset_a_template(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'sanctum');

        $this->putJson('/api/v1/admin/email-templates/order_shipped', [
            'subject' => 'পার্সেল {{order_number}} রওনা দিয়েছে', 'heading' => 'রওনা!', 'body' => "হ্যালো {{customer_name}}\n\n<script>x</script> **জরুরি**",
            'button_label' => 'দেখুন', 'button_url' => '{{track_url}}', 'is_active' => true,
        ])->assertOk();

        $html = $this->postJson('/api/v1/admin/email-templates/order_shipped/preview', [])->assertOk()->json('data.html');
        $this->assertStringContainsString('রওনা!', $html);
        $this->assertStringContainsString('<strong style="color:#111111">জরুরি</strong>', $html);
        $this->assertStringNotContainsString('<script>x</script>', $html, 'admin text is escaped');
        $this->assertStringNotContainsString('{{customer_name}}', $html);

        $this->postJson('/api/v1/admin/email-templates/order_shipped/test', ['email' => 'me@example.com'])->assertOk();
        $log = EmailLog::where('is_test', true)->latest('id')->firstOrFail();
        $this->assertSame('me@example.com', $log->to_email);
        $this->assertStringStartsWith('[টেস্ট] পার্সেল', $log->subject);

        $this->postJson('/api/v1/admin/email-templates/order_shipped/reset')->assertOk();
        $this->assertSame('আপনার অর্ডার {{order_number}} পথে আছে', EmailTemplate::where('key', 'order_shipped')->value('subject'));

        $this->putJson('/api/v1/admin/email-templates/order_shipped', ['subject' => '', 'body' => '', 'is_active' => true])
            ->assertStatus(422)->assertJsonValidationErrors(['subject', 'body']);
    }

    public function test_smtp_password_is_write_only_and_hidden_from_general_settings(): void
    {
        $this->actingAs($this->admin(), 'sanctum');

        $this->putJson('/api/v1/admin/email-settings', ['enabled' => true, 'smtp_host' => '', 'from_address' => ''])
            ->assertStatus(422)->assertJsonValidationErrors(['smtp_host', 'from_address']);

        $this->putJson('/api/v1/admin/email-settings', [
            'enabled' => false, 'from_name' => 'DRAWER', 'from_address' => 'shop@example.com', 'admin_emails' => 'a@example.com',
            'smtp_host' => 'smtp.example.com', 'smtp_port' => 465, 'smtp_encryption' => 'ssl', 'smtp_username' => 'u', 'smtp_password' => 'secret-pass',
        ])->assertOk()->assertJsonPath('data.has_password', true)->assertJsonMissingPath('data.smtp_password');

        $this->assertNotSame('secret-pass', SiteSetting::where('key', 'smtp_password')->value('value'), 'stored encrypted');

        // Saving again without a password keeps the old one.
        $this->putJson('/api/v1/admin/email-settings', ['enabled' => false, 'smtp_password' => ''])->assertOk()->assertJsonPath('data.has_password', true);

        $general = $this->getJson('/api/v1/admin/settings')->assertOk()->json('data');
        $this->assertArrayNotHasKey('smtp_password', $general);
        $this->assertArrayNotHasKey('smtp_host', $general);
    }
}
