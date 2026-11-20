<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminProductCatalogTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->actingAs(User::where('role', 'admin')->firstOrFail(), 'sanctum');
    }

    private function product(): Product
    {
        return Product::has('variants', '>=', 2)->with('variants')->firstOrFail();
    }

    private function payload(Product $p, array $variants): array
    {
        return [
            'name' => $p->name, 'price' => (float) $p->price, 'sale_price' => $p->sale_price ? (float) $p->sale_price : null,
            'category_id' => $p->category_id, 'status' => $p->status, 'variants' => $variants,
        ];
    }

    public function test_editing_a_product_keeps_existing_variant_ids(): void
    {
        $p = $this->product();
        [$keep, $drop] = [$p->variants[0], $p->variants[1]];

        $this->putJson("/api/v1/admin/products/{$p->id}", $this->payload($p, [
            ['id' => $keep->id, 'size' => $keep->size, 'color' => $keep->color, 'sku' => $keep->sku, 'stock' => 7],
            ['size' => 'XXL', 'color' => 'Olive', 'color_code' => '#556B2F', 'stock' => 3],
        ]))->assertOk();

        $p->refresh()->load('variants');
        $this->assertTrue($p->variants->contains('id', $keep->id), 'existing variant updated in place');
        $this->assertFalse($p->variants->contains('id', $drop->id), 'removed variant deleted');
        $this->assertSame(7, $p->variants->firstWhere('id', $keep->id)->stock);
        $this->assertSame('XXL / Olive', $p->variants->firstWhere('size', 'XXL')->title);
        $this->assertSame(10, $p->stock, 'product stock = sum of variants');
    }

    public function test_blank_variant_price_uses_the_product_price(): void
    {
        $p = $this->product();
        $this->putJson("/api/v1/admin/products/{$p->id}", $this->payload($p, [['size' => 'M', 'stock' => 2, 'price' => null]]))->assertOk();

        $this->assertEquals((float) $p->price, (float) $p->fresh()->variants()->first()->price);
    }

    public function test_sale_price_must_be_lower_than_price(): void
    {
        $p = $this->product();
        $this->putJson("/api/v1/admin/products/{$p->id}", ['name' => $p->name, 'price' => 500, 'sale_price' => 600])
            ->assertStatus(422)->assertJsonValidationErrors('sale_price');
    }

    public function test_duplicate_creates_a_draft_copy_with_variants(): void
    {
        $p = $this->product();
        $res = $this->postJson("/api/v1/admin/products/{$p->id}/duplicate")->assertCreated();

        $copy = Product::with('variants')->findOrFail($res->json('data.id'));
        $this->assertSame('draft', $copy->status);
        $this->assertNotSame($p->slug, $copy->slug);
        $this->assertCount($p->variants->count(), $copy->variants);
    }

    public function test_bulk_status_change_and_counts(): void
    {
        $ids = Product::where('status', 'published')->limit(2)->pluck('id')->all();
        $before = $this->getJson('/api/v1/admin/products')->json('meta.counts');

        $this->postJson('/api/v1/admin/products/bulk', ['ids' => $ids, 'action' => 'draft'])->assertOk();

        $after = $this->getJson('/api/v1/admin/products?status=draft')->assertOk();
        $this->assertSame($before['draft'] + 2, $after->json('meta.counts.draft'));
        $this->assertEqualsCanonicalizing($ids, array_intersect($ids, array_column($after->json('data'), 'id')));
    }

    public function test_unknown_sort_column_is_ignored(): void
    {
        $this->getJson('/api/v1/admin/products?sort_by=password;drop&sort_direction=sideways')->assertOk();
    }
}
