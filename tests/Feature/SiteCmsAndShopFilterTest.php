<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\SiteConfig;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class SiteCmsAndShopFilterTest extends TestCase
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

    private function validConfig(array $overrides = []): array
    {
        return array_replace_recursive(SiteConfig::defaults(), $overrides);
    }

    // ---------------------------------------------------- category showcase

    public function test_homepage_category_showcase_returns_chosen_categories_in_order(): void
    {
        $tag = Str::lower(Str::random(5));
        $catA = Category::create(['name' => "Showcase A {$tag}", 'slug' => "showcase-a-{$tag}", 'is_active' => true]);
        $catB = Category::create(['name' => "Showcase B {$tag}", 'slug' => "showcase-b-{$tag}", 'is_active' => true]);
        foreach ([$catA, $catB] as $cat) {
            foreach (range(1, 4) as $n) {
                Product::create([
                    'name' => "{$cat->name} P{$n}", 'slug' => "{$cat->slug}-p{$n}", 'sku' => "SC-{$tag}-{$cat->id}-{$n}",
                    'price' => 500, 'stock' => 3, 'category_id' => $cat->id, 'status' => 'published',
                    'featured_image' => "/storage/uploads/{$cat->slug}-{$n}.jpg",
                ]);
            }
        }
        \App\Models\HomepageSection::create([
            'type' => 'category_showcase', 'title' => "Showcase {$tag}", 'is_active' => true, 'display_order' => 99,
            'content' => ['categories' => [$catB->slug, $catA->slug], 'products_per_category' => 2],
        ]);

        $section = collect($this->getJson('/api/v1/public/homepage')->assertOk()->json('data.sections'))
            ->firstWhere('title', "Showcase {$tag}");   // the database may already hold another showcase

        $this->assertSame([$catB->slug, $catA->slug], array_column($section['showcase'], 'slug'));  // admin order kept
        $this->assertCount(2, $section['showcase'][0]['products']);                               // per-category limit
        $this->assertSame(4, $section['showcase'][0]['count']);
        $this->assertSame("/category/{$catB->slug}", $section['showcase'][0]['url']);
    }

    // ------------------------------------------------------------ site CMS

    public function test_public_site_config_returns_defaults(): void
    {
        $this->getJson('/api/v1/public/site')
            ->assertOk()
            ->assertJsonPath('data.branding.site_name', 'DRAWER CLOTHING')
            ->assertJsonStructure(['data' => ['branding', 'announcement', 'header' => ['menu'], 'footer' => ['columns'], 'contact', 'social', 'seo']]);
    }

    public function test_admin_can_update_logo_menu_and_footer(): void
    {
        $config = $this->validConfig(['branding' => ['logo' => '/storage/uploads/new-logo.png']]);
        $config['header']['menu'] = [['label' => 'শপ', 'url' => '/collections']];
        $config['footer']['columns'] = [['title' => 'সাহায্য', 'links' => [['label' => 'যোগাযোগ', 'url' => '/contact']]]];

        $this->actingAs($this->admin(), 'sanctum')->putJson('/api/v1/admin/appearance', $config)->assertOk();

        $this->getJson('/api/v1/public/site')
            ->assertJsonPath('data.branding.logo', '/storage/uploads/new-logo.png')
            ->assertJsonCount(1, 'data.header.menu')            // saved list replaces the default list
            ->assertJsonPath('data.header.menu.0.label', 'শপ')
            ->assertJsonPath('data.footer.columns.0.links.0.url', '/contact')
            ->assertJsonPath('data.contact.email', SiteConfig::defaults()['contact']['email']); // untouched sections keep defaults
    }

    public function test_about_and_contact_page_content_is_editable(): void
    {
        $config = $this->validConfig();
        $config['pages']['about']['title'] = 'নতুন শিরোনাম';
        $config['pages']['about']['stats'] = [['value' => '৫০০+', 'label' => 'খুশি গ্রাহক']];
        $config['pages']['contact']['faq'] = [['q' => 'প্রশ্ন?', 'a' => 'উত্তর।']];

        $this->actingAs($this->admin(), 'sanctum')->putJson('/api/v1/admin/appearance', $config)->assertOk();

        $this->getJson('/api/v1/public/site')
            ->assertJsonPath('data.pages.about.title', 'নতুন শিরোনাম')
            ->assertJsonCount(1, 'data.pages.about.stats')
            ->assertJsonPath('data.pages.contact.faq.0.q', 'প্রশ্ন?')
            ->assertJsonPath('data.pages.blog.title', SiteConfig::defaults()['pages']['blog']['title']);

        $bad = $this->validConfig();
        $bad['pages']['contact']['faq'] = [['q' => '', 'a' => 'x']];
        $this->actingAs($this->admin(), 'sanctum')->putJson('/api/v1/admin/appearance', $bad)
            ->assertStatus(422)->assertJsonValidationErrors(['pages.contact.faq.0.q']);
    }

    public function test_own_absolute_urls_are_saved_as_relative(): void
    {
        $config = $this->validConfig(['branding' => ['logo' => url('/storage/uploads/logo.png')]]);
        $config['header']['menu'] = [['label' => 'External', 'url' => 'https://facebook.com/shop']];

        $this->actingAs($this->admin(), 'sanctum')->putJson('/api/v1/admin/appearance', $config)->assertOk();

        $this->getJson('/api/v1/public/site')
            ->assertJsonPath('data.branding.logo', '/storage/uploads/logo.png')
            ->assertJsonPath('data.header.menu.0.url', 'https://facebook.com/shop');   // other sites untouched
    }

    public function test_appearance_validation_rejects_bad_input(): void
    {
        $bad = $this->validConfig();
        $bad['announcement']['bg_color'] = 'red; background:url(x)';
        $bad['contact']['map_url'] = 'https://evil.example.com/embed';
        $bad['header']['menu'] = [['label' => '', 'url' => '/x']];

        $this->actingAs($this->admin(), 'sanctum')->putJson('/api/v1/admin/appearance', $bad)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['announcement.bg_color', 'contact.map_url', 'header.menu.0.label']);
    }

    public function test_appearance_is_admin_only_and_resettable(): void
    {
        $this->putJson('/api/v1/admin/appearance', $this->validConfig())->assertUnauthorized();

        $config = $this->validConfig(['branding' => ['site_name' => 'Temp Name']]);
        $this->actingAs($this->admin(), 'sanctum')->putJson('/api/v1/admin/appearance', $config)->assertOk();
        $this->actingAs($this->admin(), 'sanctum')->postJson('/api/v1/admin/appearance/reset')
            ->assertOk()->assertJsonPath('data.branding.site_name', 'DRAWER CLOTHING');
    }

    // ------------------------------------------------------ settings fixes

    public function test_public_settings_never_expose_secrets(): void
    {
        SiteSetting::updateOrCreate(['key' => 'smtp_password'], ['value' => 'super-secret', 'group' => 'email', 'type' => 'text']);
        SiteSetting::updateOrCreate(['key' => 'bkash_app_secret'], ['value' => 'bk-secret', 'group' => 'payment', 'type' => 'text']);

        $body = $this->getJson('/api/v1/public/settings')->assertOk()->getContent();

        $this->assertStringNotContainsString('super-secret', $body);
        $this->assertStringNotContainsString('bk-secret', $body);
        $this->assertStringContainsString('site_name', $body);
    }

    public function test_settings_page_flat_payload_is_saved(): void
    {
        $this->actingAs($this->admin(), 'sanctum')
            ->postJson('/api/v1/admin/settings', ['currency_symbol' => '৳', 'currency_code' => 'BDT', 'appearance' => 'must-be-ignored'])
            ->assertOk();

        $this->assertSame('৳', SiteSetting::where('key', 'currency_symbol')->value('value'));
        $this->assertNotSame('must-be-ignored', SiteSetting::where('key', 'appearance')->value('value'));
    }

    // ---------------------------------------------------------- shop filter

    private function makeProduct(string $name, float $price, ?float $sale, array $sizes, ?Category $cat = null): Product
    {
        $cat ??= Category::firstOrFail();
        $product = Product::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'sku' => 'FLT-'.Str::upper(Str::random(6)),
            'price' => $price,
            'sale_price' => $sale,
            'stock' => 10,
            'category_id' => $cat->id,
            'status' => 'published',
        ]);
        foreach ($sizes as $s) {
            $product->variants()->create(['sku' => $product->sku.'-'.$s, 'title' => "{$name} / {$s}", 'size' => $s, 'price' => $price, 'stock' => 5, 'is_active' => true]);
        }

        return $product;
    }

    public function test_multi_size_and_effective_price_filters(): void
    {
        $tag = 'Zfilter'.Str::random(4);
        $a = $this->makeProduct("$tag A", 1000, 600, ['ZS1']);
        $b = $this->makeProduct("$tag B", 800, null, ['ZS2']);
        $c = $this->makeProduct("$tag C", 2000, null, ['ZS3']);

        $slugs = fn ($q) => collect($this->getJson("/api/v1/public/products?search={$tag}&per_page=50&{$q}")->json('data'))->pluck('slug')->all();

        $this->assertEqualsCanonicalizing([$a->slug, $b->slug], $slugs('size=ZS1,ZS2'));
        // A costs 600 on sale, so a 500–700 range finds it even though its list price is 1000.
        $this->assertSame([$a->slug], $slugs('min_price=500&max_price=700'));
        $this->assertSame([$a->slug], $slugs('on_sale=1'));
        // Effective-price sort: A(600) < B(800) < C(2000)
        $this->assertSame([$a->slug, $b->slug, $c->slug], $slugs('sort=price_asc'));
    }

    public function test_facets_count_each_option_ignoring_its_own_filter(): void
    {
        $tag = 'Zfacet'.Str::random(4);
        $this->makeProduct("$tag A", 500, null, ['ZF1', 'ZF2']);
        $this->makeProduct("$tag B", 900, 700, ['ZF2']);

        $facets = $this->getJson("/api/v1/public/products/facets?search={$tag}&size=ZF1")->assertOk()->json('data');

        $this->assertSame(1, $facets['total']);                                   // only A has ZF1
        $sizes = collect($facets['sizes'])->pluck('count', 'value');
        $this->assertSame(1, $sizes['ZF1']);                                      // size counts ignore the size filter itself...
        $this->assertSame(2, $sizes['ZF2']);                                      // ...so ZF2 still shows both products
        $this->assertEquals(500, $facets['price']['min']);
        $this->assertSame(0, $facets['on_sale']);                                 // A (the only ZF1 product) is not on sale
    }
}
