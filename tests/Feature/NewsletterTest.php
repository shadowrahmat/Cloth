<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_subscribe_saves_a_normalised_email_once(): void
    {
        $this->postJson('/api/v1/public/newsletter/subscribe', ['email' => '  Rahim@Example.COM '])
            ->assertOk()->assertJsonPath('already', false);

        $this->postJson('/api/v1/public/newsletter/subscribe', ['email' => 'rahim@example.com'])
            ->assertOk()->assertJsonPath('already', true);

        $this->assertSame(1, NewsletterSubscriber::where('email', 'rahim@example.com')->count());
    }

    public function test_someone_who_unsubscribed_can_join_again(): void
    {
        NewsletterSubscriber::create(['email' => 'back@example.com', 'is_active' => false, 'subscribed_at' => now()->subYear()]);

        $this->postJson('/api/v1/public/newsletter/subscribe', ['email' => 'back@example.com'])
            ->assertOk()->assertJsonPath('already', false);

        $this->assertTrue((bool) NewsletterSubscriber::where('email', 'back@example.com')->value('is_active'));
    }

    public function test_bad_email_gets_a_bangla_message_and_bots_are_ignored(): void
    {
        $this->postJson('/api/v1/public/newsletter/subscribe', ['email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'সঠিক ইমেইল ঠিকানা লিখুন (যেমন name@gmail.com)।');

        $this->postJson('/api/v1/public/newsletter/subscribe', ['email' => 'bot@example.com', 'website' => 'http://spam.example'])
            ->assertOk();
        $this->assertFalse(NewsletterSubscriber::where('email', 'bot@example.com')->exists());
    }

    public function test_admin_can_download_subscribers_as_csv(): void
    {
        NewsletterSubscriber::create(['email' => 'csv@example.com', 'is_active' => true, 'subscribed_at' => now()]);

        $res = $this->actingAs(User::where('role', 'admin')->firstOrFail(), 'sanctum')
            ->get('/api/v1/admin/newsletter-subscribers/export')
            ->assertOk();

        $csv = $res->streamedContent();
        $this->assertStringStartsWith('email,status,subscribed_at', $csv);
        $this->assertStringContainsString('csv@example.com,subscribed,', $csv);
    }

    public function test_guests_cannot_download_the_subscriber_list(): void
    {
        $this->getJson('/api/v1/admin/newsletter-subscribers/export')->assertStatus(401);
    }
}
