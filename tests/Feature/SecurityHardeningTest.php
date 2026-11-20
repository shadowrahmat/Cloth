<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('role', 'admin')->first();
    }

    public function test_public_product_api_hides_cost_price_but_admin_sees_it(): void
    {
        $product = Product::where('status', 'published')->first();
        $product->update(['cost_price' => 123]);

        $public = $this->getJson("/api/v1/public/products/{$product->slug}")->assertOk();
        $this->assertArrayNotHasKey('cost_price', $public->json('data.product'));

        $list = $this->getJson('/api/v1/public/products?per_page=50')->assertOk();
        foreach ($list->json('data') as $p) {
            $this->assertArrayNotHasKey('cost_price', $p);
        }

        $admin = $this->actingAs($this->admin(), 'sanctum')->getJson("/api/v1/admin/products/{$product->id}")->assertOk();
        $this->assertEquals(123, $admin->json('data.cost_price'));
    }

    public function test_public_product_page_hides_reviewer_email(): void
    {
        $product = Product::where('status', 'published')->first();
        $product->reviews()->create([
            'author_name' => 'Rahim', 'author_email' => 'rahim@example.test',
            'rating' => 5, 'review' => 'Great fit', 'is_approved' => true,
        ]);

        $res = $this->getJson("/api/v1/public/products/{$product->slug}")->assertOk();
        $this->assertStringNotContainsString('rahim@example.test', $res->getContent());
    }

    public function test_media_upload_rejects_svg_html_and_php(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin(), 'sanctum');

        // Real temp files (not UploadedFile::fake()), so the MIME type is sniffed from
        // the content exactly as in production: a PHP script renamed to .jpg must fail.
        $bad = [
            'logo.svg' => '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
            'page.html' => '<!DOCTYPE html><html><body><script>alert(1)</script></body></html>',
            'shell.php' => "<?php\necho 1;\n",
            'shell.php.jpg' => "<?php\necho 1;\n",
        ];

        foreach ($bad as $name => $content) {
            foreach (['files', 'file'] as $field) {
                $tmp = tempnam(sys_get_temp_dir(), 'upl');
                file_put_contents($tmp, $content);
                $file = new UploadedFile($tmp, $name, null, null, true);

                $this->postJson('/api/v1/admin/media/upload', [$field => $field === 'files' ? [$file] : $file])
                    ->assertStatus(422);
                @unlink($tmp);
            }
        }

        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_media_upload_accepts_real_images(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin(), 'sanctum');

        $res = $this->postJson('/api/v1/admin/media/upload', [
            'files' => [UploadedFile::fake()->image('Summer Shirt.jpg', 400, 400)],
        ])->assertCreated();

        $path = $res->json('data.0.path');
        $this->assertStringEndsWith('.jpg', $path);
        Storage::disk('public')->assertExists($path);
    }
}
