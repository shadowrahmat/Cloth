<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    private const BASE = '/api/v1/public';

    /** Calls the endpoint with one guest session until it is refused; returns how many calls got through. */
    private function allowedCalls(string $method, string $uri, array $body = [], int $max = 200): int
    {
        $session = (string) Str::uuid();

        for ($i = 0; $i < $max; $i++) {
            $status = $this->withHeader('X-Session-ID', $session)->json($method, self::BASE.$uri, $body)->getStatusCode();
            if ($status === 429) {
                return $i;
            }
        }

        return $max;
    }

    public function test_track_order_allows_five_a_minute_then_returns_429(): void
    {
        $this->assertSame(5, $this->allowedCalls('POST', '/track-order', ['order_number' => 'X', 'email' => 'a@b.test']));
    }

    public function test_coupon_allows_ten_a_minute(): void
    {
        $this->assertSame(10, $this->allowedCalls('POST', '/cart/coupon', ['code' => 'NOPE']));
    }

    public function test_contact_allows_five_a_minute(): void
    {
        $this->assertSame(5, $this->allowedCalls('POST', '/contact', []));
    }

    public function test_checkout_allows_ten_a_minute(): void
    {
        $this->assertSame(10, $this->allowedCalls('POST', '/checkout', []));
    }

    public function test_cart_allows_sixty_a_minute(): void
    {
        $this->assertSame(60, $this->allowedCalls('GET', '/cart'));
    }

    public function test_the_429_says_when_to_retry(): void
    {
        $session = (string) Str::uuid();
        for ($i = 0; $i < 5; $i++) {
            $this->withHeader('X-Session-ID', $session)->postJson(self::BASE.'/track-order', []);
        }

        $this->withHeader('X-Session-ID', $session)->postJson(self::BASE.'/track-order', [])
            ->assertStatus(429)->assertHeader('Retry-After');
    }

    public function test_another_guest_on_the_same_ip_is_not_blocked_by_one_noisy_session(): void
    {
        $noisy = (string) Str::uuid();
        for ($i = 0; $i < 6; $i++) {
            $this->withHeader('X-Session-ID', $noisy)->postJson(self::BASE.'/track-order', []);
        }

        $this->withHeader('X-Session-ID', (string) Str::uuid())->postJson(self::BASE.'/track-order', [])
            ->assertStatus(422);
    }

    public function test_inventing_a_new_session_id_each_time_does_not_get_around_the_ip_limit(): void
    {
        $through = 0;
        for ($i = 0; $i < 60; $i++) {
            $status = $this->withHeader('X-Session-ID', (string) Str::uuid())->postJson(self::BASE.'/track-order', [])->getStatusCode();
            if ($status === 429) {
                break;
            }
            $through++;
        }

        $this->assertSame(25, $through);
    }
}
