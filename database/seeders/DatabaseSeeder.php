<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Coupon;
use App\Models\HomepageSection;
use App\Models\InventoryMovement;
use App\Models\Page;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\ShippingMethod;
use App\Models\SiteSetting;
use App\Models\SocialGalleryItem;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles & Permissions
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], [
            'name' => 'Super Administrator',
            'description' => 'Unrestricted access to all modules and configurations',
        ]);

        $managerRole = Role::firstOrCreate(['slug' => 'manager'], [
            'name' => 'Store Manager',
            'description' => 'Can manage products, orders, inventory, and content',
        ]);

        $customerRole = Role::firstOrCreate(['slug' => 'customer'], [
            'name' => 'Customer',
            'description' => 'Standard storefront shopping account',
        ]);

        $modules = ['products', 'orders', 'inventory', 'customers', 'categories', 'cms', 'settings', 'media', 'users'];
        $permissionsList = [];
        foreach ($modules as $mod) {
            foreach (['view', 'create', 'update', 'delete'] as $act) {
                $perm = Permission::firstOrCreate(['slug' => "{$act}_{$mod}"], [
                    'name' => ucfirst($act) . ' ' . ucfirst($mod),
                    'module' => $mod,
                    'description' => "Permission to {$act} {$mod}",
                ]);
                $permissionsList[] = $perm->id;
            }
        }
        $adminRole->permissions()->sync($permissionsList);
        $managerRole->permissions()->sync(array_slice($permissionsList, 0, 24));

        // 2. Initial Users
        $admin = User::firstOrCreate(['email' => 'admin@cloth.test'], [
            'name' => 'Alexander Vance',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+1 (555) 019-2834',
            'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80',
            'is_active' => true,
        ]);
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $customer = User::firstOrCreate(['email' => 'customer@cloth.test'], [
            'name' => 'Elena Rostova',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '+1 (555) 048-9123',
            'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80',
            'is_active' => true,
        ]);
        $customer->roles()->syncWithoutDetaching([$customerRole->id]);

        // Customer default address
        $customer->addresses()->firstOrCreate([
            'first_name' => 'Elena',
            'last_name' => 'Rostova',
            'address_line_1' => '742 Evergreen Terrace, Suite 4B',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'United States',
            'phone' => '+1 (555) 048-9123',
            'is_default' => true,
        ]);

        // 3. Brands
        $brands = [
            ['name' => 'Atelier Heritage', 'slug' => 'atelier-heritage', 'description' => 'Timeless architectural tailoring crafted in Northern Italy.'],
            ['name' => 'Maison Nova', 'slug' => 'maison-nova', 'description' => 'Contemporary minimalist silhouettes with sustainable silk and wool.'],
            ['name' => 'Sartorial Studio', 'slug' => 'sartorial-studio', 'description' => 'Precision bespoke cuts for modern sartorialists.'],
            ['name' => 'Nordic Loom', 'slug' => 'nordic-loom', 'description' => 'Pure Scandinavian cashmere, wool knits, and weatherproof outerwear.'],
        ];
        $brandMap = [];
        foreach ($brands as $b) {
            $brandMap[$b['slug']] = Brand::firstOrCreate(['slug' => $b['slug']], $b);
        }

        // 4. Categories & Subcategories (Men, Women, Kids, Winter)
        $parentCategories = [
            [
                'name' => 'Men',
                'slug' => 'men',
                'description' => 'Sophisticated menswear featuring precision tailoring, fine knitwear, and clean silhouettes.',
                'image' => 'https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=800&q=80',
                'banner_image' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=1600&q=80',
                'display_order' => 1,
                'is_featured' => true,
                'children' => [
                    ['name' => 'Tailored Suits & Blazers', 'slug' => 'men-blazers', 'description' => 'Impeccable wool and linen blazers.'],
                    ['name' => 'Shirts & Oxfords', 'slug' => 'men-shirts', 'description' => 'Crisp Egyptian cotton dress shirts and casual linens.'],
                    ['name' => 'Premium Tees & Polos', 'slug' => 'men-t-shirts', 'description' => 'Supima cotton essentials.'],
                    ['name' => 'Knitwear & Sweaters', 'slug' => 'men-knitwear', 'description' => 'Merino and cashmere crewnecks.'],
                    ['name' => 'Trousers & Chinos', 'slug' => 'men-trousers', 'description' => 'Pleated wool and tailored cotton trousers.'],
                    ['name' => 'Jackets & Coats', 'slug' => 'men-jackets', 'description' => 'Structured topcoats and casual jackets.'],
                ],
            ],
            [
                'name' => 'Women',
                'slug' => 'women',
                'description' => 'Effortless luxury designed for the modern woman, from fluid silks to structured outerwear.',
                'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=800&q=80',
                'banner_image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1600&q=80',
                'display_order' => 2,
                'is_featured' => true,
                'children' => [
                    ['name' => 'Silk & Evening Dresses', 'slug' => 'women-dresses', 'description' => 'Sculptural column dresses and bias-cut slips.'],
                    ['name' => 'Tailored Blazers & Coats', 'slug' => 'women-blazers', 'description' => 'Double-breasted Italian wool blazers.'],
                    ['name' => 'Cashmere Knitwear', 'slug' => 'women-knitwear', 'description' => 'Plush turtlenecks and relaxed cardigans.'],
                    ['name' => 'Blouses & Silk Tops', 'slug' => 'women-tops', 'description' => 'Elevated shirting and gathered silk blouses.'],
                    ['name' => 'Skirts & Trousers', 'slug' => 'women-skirts', 'description' => 'Wide-leg trousers and pleated midi skirts.'],
                ],
            ],
            [
                'name' => 'Kids',
                'slug' => 'kids',
                'description' => 'Heirloom quality clothing for children, crafted from soft organic fibers and timeless palettes.',
                'image' => 'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&w=800&q=80',
                'banner_image' => 'https://images.unsplash.com/photo-1471286174890-9c112ffca564?auto=format&fit=crop&w=1600&q=80',
                'display_order' => 3,
                'is_featured' => true,
                'children' => [
                    ['name' => 'Boys Tailoring & Shirts', 'slug' => 'kids-boys', 'description' => 'Smart shirting and comfortable knit pants.'],
                    ['name' => 'Girls Dresses & Sets', 'slug' => 'kids-girls', 'description' => 'Linen pinafores and soft cotton dresses.'],
                    ['name' => 'Kids Cashmere & Knits', 'slug' => 'kids-knitwear', 'description' => 'Ultra-gentle merino and organic cotton sweaters.'],
                    ['name' => 'Everyday Essentials', 'slug' => 'kids-essentials', 'description' => 'Breathable organic tees and rompers.'],
                ],
            ],
            [
                'name' => 'Winter',
                'slug' => 'winter',
                'description' => 'The Winter Edit. Pure Mongolian cashmere, heavy wool trench coats, and refined thermal layers.',
                'image' => 'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=800&q=80',
                'banner_image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1600&q=80',
                'display_order' => 4,
                'is_featured' => true,
                'children' => [
                    ['name' => 'Pure Wool Overcoats', 'slug' => 'winter-overcoats', 'description' => 'Full-length double-faced wool topcoats.'],
                    ['name' => 'Cashmere Sweaters & Knits', 'slug' => 'winter-cashmere', 'description' => '100% Grade-A Mongolian cashmere.'],
                    ['name' => 'Down & Puffer Jackets', 'slug' => 'winter-puffers', 'description' => 'Minimalist matte goose-down outerwear.'],
                    ['name' => 'Shearling & Leather', 'slug' => 'winter-shearling', 'description' => 'Warm Toscana shearling coats.'],
                    ['name' => 'Winter Scarves & Accessories', 'slug' => 'winter-accessories', 'description' => 'Cashmere wraps, beanies, and leather gloves.'],
                ],
            ],
        ];

        $categoryMap = [];
        foreach ($parentCategories as $pCat) {
            $children = $pCat['children'];
            unset($pCat['children']);

            $parent = Category::firstOrCreate(['slug' => $pCat['slug']], $pCat);
            $categoryMap[$pCat['slug']] = $parent;

            foreach ($children as $cOrder => $cCat) {
                $cCat['parent_id'] = $parent->id;
                $cCat['display_order'] = $cOrder + 1;
                $cCat['is_active'] = true;
                $child = Category::firstOrCreate(['slug' => $cCat['slug']], $cCat);
                $categoryMap[$cCat['slug']] = $child;
            }
        }

        // 5. Collections
        $collectionsData = [
            [
                'title' => 'Autumn / Winter Editorial',
                'slug' => 'autumn-winter',
                'description' => 'A sculptural dialogue between architectural volume and soft luxury.',
                'banner_image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1600&q=80',
                'is_featured' => true,
                'display_order' => 1,
            ],
            [
                'title' => 'The Minimalist Capsule',
                'slug' => 'minimalist-capsule',
                'description' => 'Twelve foundational pieces engineered for versatile everyday luxury.',
                'banner_image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=1600&q=80',
                'is_featured' => true,
                'display_order' => 2,
            ],
            [
                'title' => 'Pure Cashmere Collection',
                'slug' => 'pure-cashmere',
                'description' => 'Unmatched softness sustainably sourced from the Mongolian steppes.',
                'banner_image' => 'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=1600&q=80',
                'is_featured' => true,
                'display_order' => 3,
            ],
            [
                'title' => 'Sartorial Tailoring',
                'slug' => 'sartorial-tailoring',
                'description' => 'Modern suit jackets, pleated trousers, and structured overcoats.',
                'banner_image' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=1600&q=80',
                'is_featured' => true,
                'display_order' => 4,
            ],
        ];
        $collectionMap = [];
        foreach ($collectionsData as $col) {
            $collectionMap[$col['slug']] = Collection::firstOrCreate(['slug' => $col['slug']], $col);
        }

        // 6. Products with Variants
        $productsSeed = [
            [
                'name' => 'The Double-Faced Cashmere Overcoat',
                'slug' => 'double-faced-cashmere-overcoat',
                'sku' => 'ATL-OVC-001',
                'price' => 890.00,
                'sale_price' => null,
                'compare_at_price' => 1100.00,
                'category_slug' => 'winter-overcoats',
                'brand_slug' => 'atelier-heritage',
                'featured_image' => 'https://images.unsplash.com/photo-1544022613-e87ca75a784a?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1544022613-e87ca75a784a?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Unlined double-faced cashmere coat with hand-stitched edges and horn buttons.',
                'description' => 'Crafted from the softest grade-A Mongolian cashmere, this full-length coat features relaxed raglan sleeves, a concealed placket, and deep patch pockets. Hand-finished by master tailors in Biella, Italy.',
                'fabric_care' => '100% Cashmere. Specialized luxury dry clean only. Store in breathable garment cover.',
                'shipping_info' => 'Complimentary worldwide insured shipping. Arrives in a bespoke presentation box.',
                'returns_info' => 'Complimentary 30-day home collection return service.',
                'is_featured' => true,
                'is_bestseller' => true,
                'is_new' => true,
                'stock' => 24,
                'variants' => [
                    ['size' => 'S', 'color' => 'Sand', 'color_code' => '#D8CCBC', 'stock' => 6],
                    ['size' => 'M', 'color' => 'Sand', 'color_code' => '#D8CCBC', 'stock' => 8],
                    ['size' => 'L', 'color' => 'Sand', 'color_code' => '#D8CCBC', 'stock' => 5],
                    ['size' => 'M', 'color' => 'Black', 'color_code' => '#111111', 'stock' => 5],
                ],
            ],
            [
                'name' => 'Italian Wool Sartorial Blazer',
                'slug' => 'italian-wool-sartorial-blazer',
                'sku' => 'ATL-BLZ-002',
                'price' => 580.00,
                'sale_price' => 520.00,
                'compare_at_price' => 650.00,
                'category_slug' => 'men-blazers',
                'brand_slug' => 'sartorial-studio',
                'featured_image' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Deconstructed two-button jacket in Super 130s virgin wool with peak lapels.',
                'description' => 'Lightweight floating canvas construction ensures effortless drape while retaining sharp structure. Featuring roped shoulders and natural horn buttons.',
                'fabric_care' => '100% Super 130s Virgin Wool. Dry clean only.',
                'shipping_info' => 'Complimentary express delivery in 2-3 business days.',
                'returns_info' => 'Free 30-day returns with pre-printed return labels.',
                'is_featured' => true,
                'is_bestseller' => true,
                'is_new' => false,
                'stock' => 30,
                'variants' => [
                    ['size' => '38R', 'color' => 'Navy', 'color_code' => '#1B2A4A', 'stock' => 8],
                    ['size' => '40R', 'color' => 'Navy', 'color_code' => '#1B2A4A', 'stock' => 10],
                    ['size' => '42R', 'color' => 'Navy', 'color_code' => '#1B2A4A', 'stock' => 7],
                    ['size' => '40R', 'color' => 'Taupe', 'color_code' => '#A89B8A', 'stock' => 5],
                ],
            ],
            [
                'name' => 'Sculptural Bias-Cut Silk Dress',
                'slug' => 'sculptural-bias-cut-silk-dress',
                'sku' => 'ATL-DRS-003',
                'price' => 460.00,
                'sale_price' => null,
                'compare_at_price' => 550.00,
                'category_slug' => 'women-dresses',
                'brand_slug' => 'maison-nova',
                'featured_image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Heavyweight 22-momme Mulberry silk slip dress with cowl neckline.',
                'description' => 'Cut diagonally across the grain to gently mold to the body curves with fluid movement. Features a subtle scoop back and delicate hand-rolled hems.',
                'fabric_care' => '100% Mulberry Silk. Delicate cold hand wash or dry clean.',
                'shipping_info' => 'Ships next business day in signature gift packaging.',
                'returns_info' => 'Free returns and exchanges within 30 days.',
                'is_featured' => true,
                'is_bestseller' => false,
                'is_new' => true,
                'stock' => 18,
                'variants' => [
                    ['size' => 'XS', 'color' => 'Ivory', 'color_code' => '#F7F5F0', 'stock' => 4],
                    ['size' => 'S', 'color' => 'Ivory', 'color_code' => '#F7F5F0', 'stock' => 6],
                    ['size' => 'M', 'color' => 'Ivory', 'color_code' => '#F7F5F0', 'stock' => 5],
                    ['size' => 'M', 'color' => 'Black', 'color_code' => '#111111', 'stock' => 3],
                ],
            ],
            [
                'name' => 'Chunky Cashmere Roll-Neck Sweater',
                'slug' => 'chunky-cashmere-roll-neck-sweater',
                'sku' => 'ATL-KNW-004',
                'price' => 380.00,
                'sale_price' => null,
                'compare_at_price' => null,
                'category_slug' => 'winter-cashmere',
                'brand_slug' => 'nordic-loom',
                'featured_image' => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => '4-ply ribbed turtleneck in un-dyed Mongolian cashmere.',
                'description' => 'Knitted on 5-gauge machines with substantial weight and cloud-like warmth. Raglan seams and deep ribbed cuffs designed to fold over.',
                'fabric_care' => '100% Cashmere. Hand wash in tepid water with wool detergent. Dry flat.',
                'shipping_info' => 'Free standard delivery on orders over $150.',
                'returns_info' => 'Full refund within 30 days of delivery.',
                'is_featured' => true,
                'is_bestseller' => true,
                'is_new' => true,
                'stock' => 28,
                'variants' => [
                    ['size' => 'S', 'color' => 'Ivory', 'color_code' => '#F7F5F0', 'stock' => 8],
                    ['size' => 'M', 'color' => 'Ivory', 'color_code' => '#F7F5F0', 'stock' => 10],
                    ['size' => 'L', 'color' => 'Ivory', 'color_code' => '#F7F5F0', 'stock' => 6],
                    ['size' => 'M', 'color' => 'Taupe', 'color_code' => '#A89B8A', 'stock' => 4],
                ],
            ],
            [
                'name' => 'Pleated Wool Wide-Leg Trousers',
                'slug' => 'pleated-wool-wide-leg-trousers',
                'sku' => 'ATL-TRS-005',
                'price' => 320.00,
                'sale_price' => 280.00,
                'compare_at_price' => 360.00,
                'category_slug' => 'men-trousers',
                'brand_slug' => 'sartorial-studio',
                'featured_image' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'High-rise pleated pants in English wool flannel with side adjusters.',
                'description' => 'Deep forward double pleats create an elegant drape through the relaxed leg. Finished with discreet side buckle tabs and an unhemmed cuff for bespoke tailor adjustments.',
                'fabric_care' => '100% Wool Flannel. Dry clean only.',
                'shipping_info' => 'Express delivery available.',
                'returns_info' => 'Free 30-day returns.',
                'is_featured' => false,
                'is_bestseller' => true,
                'is_new' => false,
                'stock' => 22,
                'variants' => [
                    ['size' => '30', 'color' => 'Sand', 'color_code' => '#D8CCBC', 'stock' => 5],
                    ['size' => '32', 'color' => 'Sand', 'color_code' => '#D8CCBC', 'stock' => 9],
                    ['size' => '34', 'color' => 'Sand', 'color_code' => '#D8CCBC', 'stock' => 8],
                ],
            ],
            [
                'name' => 'Minimalist Puffer Down Coat',
                'slug' => 'minimalist-puffer-down-coat',
                'sku' => 'ATL-PFR-006',
                'price' => 640.00,
                'sale_price' => null,
                'compare_at_price' => null,
                'category_slug' => 'winter-puffers',
                'brand_slug' => 'nordic-loom',
                'featured_image' => 'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Water-repellent matte Japanese nylon jacket with 800-fill European goose down.',
                'description' => 'Engineered for sub-zero conditions without sacrificing sleek architectural aesthetics. Concealed storm cuffs, fleece-lined hand pockets, and magnetic storm flap closure.',
                'fabric_care' => 'Shell: 100% Recycled Nylon. Fill: 90% Goose Down, 10% Feathers. Gentle cycle machine wash.',
                'shipping_info' => 'Free worldwide insured courier delivery.',
                'returns_info' => '30-day return policy.',
                'is_featured' => true,
                'is_bestseller' => false,
                'is_new' => true,
                'stock' => 16,
                'variants' => [
                    ['size' => 'S', 'color' => 'Black', 'color_code' => '#111111', 'stock' => 4],
                    ['size' => 'M', 'color' => 'Black', 'color_code' => '#111111', 'stock' => 8],
                    ['size' => 'L', 'color' => 'Black', 'color_code' => '#111111', 'stock' => 4],
                ],
            ],
            [
                'name' => 'Kids Cashmere Cable-Knit Cardigan',
                'slug' => 'kids-cashmere-cable-knit-cardigan',
                'sku' => 'ATL-KID-007',
                'price' => 195.00,
                'sale_price' => null,
                'compare_at_price' => 240.00,
                'category_slug' => 'kids-knitwear',
                'brand_slug' => 'atelier-heritage',
                'featured_image' => 'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Hand-knit organic merino & cashmere buttoned sweater for boys and girls.',
                'description' => 'Gentle on delicate skin with natural hypoallergenic properties. Finished with sustainable mother-of-pearl buttons.',
                'fabric_care' => '70% Organic Merino, 30% Cashmere. Hand wash cold.',
                'shipping_info' => 'Ships within 24 hours.',
                'returns_info' => 'Free 30-day returns.',
                'is_featured' => true,
                'is_bestseller' => false,
                'is_new' => true,
                'stock' => 20,
                'variants' => [
                    ['size' => '2-3Y', 'color' => 'Ivory', 'color_code' => '#F7F5F0', 'stock' => 6],
                    ['size' => '4-5Y', 'color' => 'Ivory', 'color_code' => '#F7F5F0', 'stock' => 8],
                    ['size' => '6-7Y', 'color' => 'Sand', 'color_code' => '#D8CCBC', 'stock' => 6],
                ],
            ],
            [
                'name' => 'Egyptian Cotton Structured Oxford Shirt',
                'slug' => 'egyptian-cotton-oxford-shirt',
                'sku' => 'ATL-SHT-008',
                'price' => 185.00,
                'sale_price' => null,
                'compare_at_price' => null,
                'category_slug' => 'men-shirts',
                'brand_slug' => 'atelier-heritage',
                'featured_image' => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Heavyweight basket-weave Oxford shirting with tailored French seams.',
                'description' => 'Spun from Giza 87 Egyptian cotton yarns for a soft, structured feel that softens with every wash. Mother-of-pearl buttons and curved hemline.',
                'fabric_care' => '100% Giza Cotton. Machine wash warm, hang dry.',
                'shipping_info' => 'Standard and express shipping options available.',
                'returns_info' => '30-day return policy.',
                'is_featured' => false,
                'is_bestseller' => true,
                'is_new' => false,
                'stock' => 45,
                'variants' => [
                    ['size' => '15.5', 'color' => 'Pure White', 'color_code' => '#FFFFFF', 'stock' => 12],
                    ['size' => '16.0', 'color' => 'Pure White', 'color_code' => '#FFFFFF', 'stock' => 18],
                    ['size' => '16.5', 'color' => 'Pure White', 'color_code' => '#FFFFFF', 'stock' => 15],
                ],
            ],
        ];

        foreach ($productsSeed as $p) {
            $cat = $categoryMap[$p['category_slug']] ?? null;
            $brd = $brandMap[$p['brand_slug']] ?? null;
            $variants = $p['variants'];
            unset($p['category_slug'], $p['brand_slug'], $p['variants']);

            $p['category_id'] = $cat?->id;
            $p['brand_id'] = $brd?->id;
            $p['status'] = 'published';
            $p['attributes'] = [
                'sizes' => array_unique(array_column($variants, 'size')),
                'colors' => array_unique(array_column($variants, 'color')),
            ];

            $product = Product::firstOrCreate(['slug' => $p['slug']], $p);

            // Variants
            foreach ($variants as $v) {
                $vSku = $product->sku . '-' . strtoupper(Str::slug($v['size'] . '-' . $v['color']));
                ProductVariant::firstOrCreate(['sku' => $vSku], [
                    'product_id' => $product->id,
                    'title' => "{$v['color']} / {$v['size']}",
                    'size' => $v['size'],
                    'color' => $v['color'],
                    'color_code' => $v['color_code'],
                    'stock' => $v['stock'],
                    'price' => $product->price,
                    'compare_at_price' => $product->compare_at_price,
                    'image' => $product->featured_image,
                    'is_active' => true,
                ]);
            }

            // Sync with sample collections
            $product->collections()->syncWithoutDetaching([
                $collectionMap['autumn-winter']->id,
                $collectionMap['minimalist-capsule']->id,
            ]);

            // Sample review
            ProductReview::firstOrCreate(['product_id' => $product->id, 'author_email' => 'client@fashion.com'], [
                'author_name' => 'Charlotte D.',
                'rating' => 5,
                'title' => 'Exquisite craftsmanship',
                'review' => 'The fabric weight and cut exceeded all my expectations. It feels truly bespoke and effortlessly elegant.',
                'is_approved' => true,
            ]);
        }

        // 7. Shipping Methods
        ShippingMethod::firstOrCreate(['code' => 'standard'], [
            'name' => 'Complimentary Standard Delivery',
            'description' => 'Delivery within 3–5 business days. Free on orders over $150.',
            'rate' => 15.00,
            'free_threshold' => 150.00,
            'estimated_delivery' => '3–5 business days',
            'is_active' => true,
        ]);

        ShippingMethod::firstOrCreate(['code' => 'express'], [
            'name' => 'White Glove Express Courier',
            'description' => 'Priority air freight with doorstep signature delivery.',
            'rate' => 25.00,
            'free_threshold' => null,
            'estimated_delivery' => '1–2 business days',
            'is_active' => true,
        ]);

        // 8. Coupons
        Coupon::firstOrCreate(['code' => 'WELCOME10'], [
            'type' => 'percentage',
            'value' => 10.00,
            'min_order_amount' => 100.00,
            'usage_limit' => 500,
            'is_active' => true,
        ]);

        Coupon::firstOrCreate(['code' => 'LUXURY20'], [
            'type' => 'percentage',
            'value' => 20.00,
            'min_order_amount' => 300.00,
            'max_discount_amount' => 200.00,
            'usage_limit' => 200,
            'is_active' => true,
        ]);

        Coupon::firstOrCreate(['code' => 'WINTER50'], [
            'type' => 'fixed',
            'value' => 50.00,
            'min_order_amount' => 250.00,
            'usage_limit' => 100,
            'is_active' => true,
        ]);

        // 9. All 13 Dynamic Homepage Sections in Exact Order
        $homepageSections = [
            [
                'type' => 'announcement',
                'title' => 'Announcement Bar',
                'subtitle' => 'Top header ticker',
                'display_order' => 1,
                'is_active' => true,
                'content' => [
                    'text' => 'Complimentary Worldwide Shipping on Orders Over $150 • Enjoy 30-Day White Glove Returns',
                    'link_url' => '/collections/autumn-winter',
                    'link_text' => 'Discover The Edit',
                    'bg_color' => '#111111',
                    'text_color' => '#FFFFFF',
                    'enabled' => true,
                ],
            ],
            [
                'type' => 'header',
                'title' => 'Header & Mega Navigation',
                'subtitle' => 'Global storefront navigation configuration',
                'display_order' => 2,
                'is_active' => true,
                'content' => [
                    'logo_text' => 'ATELIER',
                    'tagline' => 'HAUTE COUTURE & READY-TO-WEAR',
                    'show_search' => true,
                    'show_wishlist' => true,
                    'show_account' => true,
                ],
            ],
            [
                'type' => 'hero',
                'title' => 'Hero Campaign Banner',
                'subtitle' => 'Primary luxury campaign editorial',
                'display_order' => 3,
                'is_active' => true,
                'content' => [
                    'headline' => 'A New Perspective on Modern Style',
                    'subheadline' => 'Discover thoughtfully curated essentials designed for contemporary living. Architectural tailoring crafted with Italian wool and pure Mongolian cashmere.',
                    'primary_cta_text' => 'Explore The Collection',
                    'primary_cta_link' => '/collection/winter',
                    'secondary_cta_text' => 'Shop New Arrivals',
                    'secondary_cta_link' => '/collection/new-arrivals',
                    'desktop_image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1920&q=85',
                    'mobile_image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=800&q=85',
                    'text_alignment' => 'left',
                    'overlay_opacity' => 35,
                ],
            ],
            [
                'type' => 'category_grid',
                'title' => 'Featured Categories',
                'subtitle' => 'Curated department showcases',
                'display_order' => 4,
                'is_active' => true,
                'content' => [
                    'heading' => 'Curated Departments',
                    'description' => 'Explore masterfully crafted garments structured around timeless silhouettes.',
                ],
            ],
            [
                'type' => 'new_arrivals',
                'title' => 'New Arrivals',
                'subtitle' => 'Recently released runway pieces',
                'display_order' => 5,
                'is_active' => true,
                'content' => [
                    'heading' => 'New Season Releases',
                    'description' => 'Fresh architectural silhouettes crafted for transitional seasonal styling.',
                    'limit' => 8,
                ],
            ],
            [
                'type' => 'editorial',
                'title' => 'Editorial Campaign',
                'subtitle' => 'The Art of Everyday Dressing',
                'display_order' => 6,
                'is_active' => true,
                'content' => [
                    'heading' => 'The Art of Everyday Dressing',
                    'quote' => 'Style is more than what you wear. It is how you express your inner calm and deliberate presence.',
                    'description' => 'Our Autumn/Winter collection centers on tactile warmth—unlined double-faced cashmere coats, fluid silk slips, and precision-cut virgin wool flannel.',
                    'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=1200&q=80',
                    'cta_text' => 'Read The Journal',
                    'cta_link' => '/pages/about-us',
                    'layout' => 'image_left',
                ],
            ],
            [
                'type' => 'best_sellers',
                'title' => 'Best Sellers',
                'subtitle' => 'Iconic brand staples',
                'display_order' => 7,
                'is_active' => true,
                'content' => [
                    'heading' => 'Iconic Pieces',
                    'description' => 'Celebrated classics consistently favored by our private clientele worldwide.',
                    'limit' => 4,
                ],
            ],
            [
                'type' => 'shop_the_look',
                'title' => 'Shop The Look',
                'subtitle' => 'Interactive lookbook outfit',
                'display_order' => 8,
                'is_active' => true,
                'content' => [
                    'heading' => 'The Winter Lookbook',
                    'description' => 'Hover over each piece to inspect craftsmanship details and add directly to your bag.',
                    'image' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=1400&q=80',
                    'hotspots' => [
                        [
                            'top' => '25%',
                            'left' => '52%',
                            'title' => 'Cashmere Roll-Neck Sweater',
                            'price' => '$380.00',
                            'product_slug' => 'chunky-cashmere-roll-neck-sweater',
                        ],
                        [
                            'top' => '65%',
                            'left' => '48%',
                            'title' => 'Pleated Wool Wide-Leg Trousers',
                            'price' => '$280.00',
                            'product_slug' => 'pleated-wool-wide-leg-trousers',
                        ],
                        [
                            'top' => '40%',
                            'left' => '35%',
                            'title' => 'Double-Faced Cashmere Overcoat',
                            'price' => '$890.00',
                            'product_slug' => 'double-faced-cashmere-overcoat',
                        ],
                    ],
                ],
            ],
            [
                'type' => 'brand_story',
                'title' => 'Brand Story',
                'subtitle' => 'Designed With Intention',
                'display_order' => 9,
                'is_active' => true,
                'content' => [
                    'heading' => 'Designed With Intention.',
                    'description' => 'Founded with a resolute commitment to slow fashion, ATELIER unites heritage European mills with forward-looking sustainable practice. Each garment requires over forty hours of meticulous tailoring, created to be worn and cherished for generations.',
                    'stat_1_number' => '100%',
                    'stat_1_label' => 'Certified Organic & Traceable Fibers',
                    'stat_2_number' => '40+',
                    'stat_2_label' => 'Hours of Master Artisan Tailoring',
                    'stat_3_number' => '30',
                    'stat_3_label' => 'Days Complimentary Global Returns',
                ],
            ],
            [
                'type' => 'testimonials',
                'title' => 'Customer Testimonials',
                'subtitle' => 'Reviews from discerning clients',
                'display_order' => 10,
                'is_active' => true,
                'content' => [
                    'heading' => 'Reflections from Our Clients',
                    'description' => 'Verified reviews from customers experiencing ATELIER tailoring firsthand.',
                ],
            ],
            [
                'type' => 'instagram_gallery',
                'title' => 'Instagram & Social Gallery',
                'subtitle' => '@atelier_couture',
                'display_order' => 11,
                'is_active' => true,
                'content' => [
                    'heading' => 'In Good Company',
                    'handle' => '@atelier_couture',
                    'description' => 'Tag your looks with #AtelierEveryday to be featured in our seasonal curation.',
                ],
            ],
            [
                'type' => 'newsletter',
                'title' => 'Newsletter Subscription',
                'subtitle' => 'Stay in Style',
                'display_order' => 12,
                'is_active' => true,
                'content' => [
                    'heading' => 'Stay in Style.',
                    'description' => 'Receive private invitations to seasonal private sales, runway previews, and editorial essays.',
                    'button_text' => 'Subscribe',
                    'disclaimer' => 'By subscribing you agree to receive digital communications and accept our privacy policy.',
                ],
            ],
            [
                'type' => 'footer',
                'title' => 'Footer',
                'subtitle' => 'Brand information & customer concierge',
                'display_order' => 13,
                'is_active' => true,
                'content' => [
                    'brand_name' => 'ATELIER',
                    'description' => 'Purveyor of fine tailoring, Italian knitwear, and modern wardrobe essentials.',
                    'copyright' => '© ' . date('Y') . ' ATELIER Luxury Apparel Inc. All rights reserved.',
                ],
            ],
        ];

        foreach ($homepageSections as $sec) {
            HomepageSection::firstOrCreate(['type' => $sec['type']], $sec);
        }

        // 10. Testimonials
        $testimonialsSeed = [
            [
                'customer_name' => 'Victoria Sterling',
                'rating' => 5,
                'review' => 'The Double-Faced Cashmere Coat is nothing short of sublime. The hand-finished edges and drape are equal to Savile Row houses charging three times the price.',
                'product_purchased' => 'Double-Faced Cashmere Overcoat',
                'customer_image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80',
                'is_featured' => true,
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'customer_name' => 'Jonathan Rhys',
                'rating' => 5,
                'review' => 'Finding unlined virgin wool blazers with this degree of deconstructed lightness is remarkably rare. Truly an everyday staple in my travel wardrobe.',
                'product_purchased' => 'Italian Wool Sartorial Blazer',
                'customer_image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80',
                'is_featured' => true,
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'customer_name' => 'Camille Laurent',
                'rating' => 5,
                'review' => 'The bias-cut silk dress fits like a gentle second skin. Arrived in a gorgeous cloth-wrapped presentation box within two days.',
                'product_purchased' => 'Sculptural Bias-Cut Silk Dress',
                'customer_image' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=200&q=80',
                'is_featured' => true,
                'is_active' => true,
                'display_order' => 3,
            ],
        ];
        foreach ($testimonialsSeed as $t) {
            Testimonial::firstOrCreate(['customer_name' => $t['customer_name']], $t);
        }

        // 11. Social Gallery Items
        $socialItems = [
            ['title' => 'Morning light in Milan', 'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=600&q=80', 'instagram_handle' => '@claire_v'],
            ['title' => 'Tailoring details', 'image' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=600&q=80', 'instagram_handle' => '@sartorialist_ny'],
            ['title' => 'Winter cashmere texture', 'image' => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=600&q=80', 'instagram_handle' => '@studio_atelier'],
            ['title' => 'Weekend strolls in Paris', 'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=600&q=80', 'instagram_handle' => '@parisian_notes'],
            ['title' => 'Architectural silhouettes', 'image' => 'https://images.unsplash.com/photo-1544022613-e87ca75a784a?auto=format&fit=crop&w=600&q=80', 'instagram_handle' => '@atelier_couture'],
            ['title' => 'Pure silk drape', 'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=600&q=80', 'instagram_handle' => '@modern_minimal'],
        ];
        foreach ($socialItems as $idx => $s) {
            $s['display_order'] = $idx + 1;
            $s['is_active'] = true;
            SocialGalleryItem::firstOrCreate(['image' => $s['image']], $s);
        }

        // 12. Static CMS Pages
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'template' => 'about',
                'content' => "## The ATELIER Philosophy\n\nFounded in 2020, ATELIER was born from a desire to strip away seasonal obsolescence in favor of garments that endure.\n\n### Architectural Precision\nEvery piece is drafted around the natural movement of the human form. We work exclusively with certified ethical mills across Biella, Como, and the Scottish Highlands.\n\n### Sustainable Heritage\nWe refuse synthetic polyester blends. Our garments are crafted from 100% natural, biodegradable fibers including Grade-A Mongolian cashmere, Supima cotton, and European linen.",
                'is_active' => true,
            ],
            [
                'title' => 'Client Concierge & Contact',
                'slug' => 'contact',
                'template' => 'contact',
                'content' => "Our client advisors are available Monday through Saturday, 9am to 6pm EST.\n\n**Email:** concierge@atelier.clothing\n**Phone:** +1 (800) 555-0192\n**Showroom:** 420 West Broadway, Soho, New York, NY 10012",
                'is_active' => true,
            ],
            [
                'title' => 'Shipping & White Glove Delivery',
                'slug' => 'shipping-returns',
                'template' => 'default',
                'content' => "### Complimentary Worldwide Shipping\nWe provide complimentary standard shipping on all orders exceeding $150. All shipments are fully insured and tracked.\n\n### Seamless 30-Day Returns\nIf an item does not suit your expectations, we offer complimentary home courier collection within 30 days of delivery.",
                'is_active' => true,
            ],
            [
                'title' => 'Tailoring & Size Guide',
                'slug' => 'size-guide',
                'template' => 'default',
                'content' => "### Menswear & Womenswear Sizing\n\nOur silhouettes follow classic European tailoring standards. If you fall between sizes, we recommend selecting the larger size for a relaxed drape or consulting our concierge for personalized fitting guidance.",
                'is_active' => true,
            ],
        ];
        foreach ($pages as $p) {
            Page::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // 13. Site Settings
        $settings = [
            ['key' => 'site_name', 'value' => 'ATELIER', 'group' => 'general', 'type' => 'text'],
            ['key' => 'site_tagline', 'value' => 'Haute Couture & Modern Tailoring', 'group' => 'general', 'type' => 'text'],
            ['key' => 'contact_email', 'value' => 'concierge@atelier.clothing', 'group' => 'general', 'type' => 'text'],
            ['key' => 'contact_phone', 'value' => '+1 (800) 555-0192', 'group' => 'general', 'type' => 'text'],
            ['key' => 'currency_symbol', 'value' => '$', 'group' => 'store', 'type' => 'text'],
            ['key' => 'currency_code', 'value' => 'USD', 'group' => 'store', 'type' => 'text'],
            ['key' => 'free_shipping_threshold', 'value' => '150', 'group' => 'shipping', 'type' => 'text'],
            ['key' => 'tax_rate', 'value' => '0', 'group' => 'store', 'type' => 'text'],
            ['key' => 'enable_cod', 'value' => '1', 'group' => 'payment', 'type' => 'boolean'],
            ['key' => 'enable_stripe', 'value' => '1', 'group' => 'payment', 'type' => 'boolean'],
            ['key' => 'seo_title', 'value' => 'ATELIER | Luxury Fashion & Ready-To-Wear', 'group' => 'seo', 'type' => 'text'],
            ['key' => 'seo_description', 'value' => 'Discover architectural menswear, womenswear, and the winter cashmere collection.', 'group' => 'seo', 'type' => 'text'],
        ];
        foreach ($settings as $s) {
            SiteSetting::firstOrCreate(['key' => $s['key']], $s);
        }
    }
}
