<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PopulateAllCategoryProducts extends Command
{
    protected $signature = 'cloth:populate-all-categories';
    protected $description = 'Populate complete high-end fashion products across every category and subcategory';

    public function handle(): int
    {
        $this->info('Populating curated luxury catalog for all categories...');

        $catalog = [
            // MEN: Knitwear & Sweaters
            [
                'name' => 'Italian Merino Mock-Neck Sweater',
                'category_slug' => 'men-knitwear',
                'price' => 195.00,
                'sale_price' => 175.00,
                'featured_image' => 'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1614975058789-41316d0e2e9c?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Spun from extrafine 19.5-micron Italian Merino wool with subtle ribbing.',
                'description' => 'An essential layering piece for refined winter styling. Features a streamlined mock-neck collar and tailored tubular cuffs.',
                'sizes' => ['S', 'M', 'L', 'XL'],
                'colors' => [
                    ['name' => 'Charcoal Heather', 'code' => '#333333'],
                    ['name' => 'Oatmeal', 'code' => '#E5DFD5'],
                    ['name' => 'Midnight Navy', 'code' => '#1B263B'],
                ],
                'is_bestseller' => true,
            ],
            [
                'name' => 'Ribbed Cashmere Shawl-Collar Cardigan',
                'category_slug' => 'men-knitwear',
                'price' => 285.00,
                'sale_price' => null,
                'featured_image' => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Heavyweight 5-gauge Mongolian cashmere with genuine horn buttons.',
                'description' => 'Masterfully knitted with a substantial shawl collar and deep welt pockets. Provides unparalleled warmth with relaxed sophistication.',
                'sizes' => ['M', 'L', 'XL'],
                'colors' => [
                    ['name' => 'Warm Camel', 'code' => '#C19A6B'],
                    ['name' => 'Espresso', 'code' => '#3D2B1F'],
                ],
                'is_bestseller' => false,
            ],

            // MEN: Jackets & Coats
            [
                'name' => 'Italian Suede Zip Field Jacket',
                'category_slug' => 'men-jackets',
                'price' => 650.00,
                'sale_price' => 590.00,
                'featured_image' => 'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Crafted from supple Tuscan goat suede with water-repellent finishing.',
                'description' => 'Inspired by mid-century military attire, elevated with custom gunmetal two-way hardware and dual chest bellows pockets.',
                'sizes' => ['38', '40', '42', '44'],
                'colors' => [
                    ['name' => 'Tobacco Brown', 'code' => '#704214'],
                    ['name' => 'Forest Olive', 'code' => '#4A5D4E'],
                ],
                'is_bestseller' => true,
            ],
            [
                'name' => 'Double-Breasted Heritage Trench Coat',
                'category_slug' => 'men-jackets',
                'price' => 520.00,
                'sale_price' => null,
                'featured_image' => 'https://images.unsplash.com/photo-1544022613-e87ca75a784a?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Waterproof cotton gabardine featuring horn storm flaps and horn buckle belt.',
                'description' => 'An iconic silhouette tailored with a relaxed drape, raglan sleeves for ease of layering, and deep storm welt pockets.',
                'sizes' => ['38', '40', '42', '44'],
                'colors' => [
                    ['name' => 'Classic Honey', 'code' => '#D8BC7E'],
                    ['name' => 'Noir Black', 'code' => '#111111'],
                ],
                'is_bestseller' => false,
            ],

            // WOMEN: Tailored Blazers & Coats
            [
                'name' => 'Hourglass Wool Crepe Blazer',
                'category_slug' => 'women-blazers',
                'price' => 480.00,
                'sale_price' => 430.00,
                'featured_image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Structured architectural blazer with cinched waist and peak lapels.',
                'description' => 'Tailored from heavyweight Italian virgin wool crepe that holds crisp silhouette lines while allowing fluid movement.',
                'sizes' => ['XS', 'S', 'M', 'L'],
                'colors' => [
                    ['name' => 'Ivory Cream', 'code' => '#F7F5F0'],
                    ['name' => 'Jet Noir', 'code' => '#111111'],
                ],
                'is_bestseller' => true,
            ],
            [
                'name' => 'Double-Faced Cashmere Belted Wrap Coat',
                'category_slug' => 'women-blazers',
                'price' => 890.00,
                'sale_price' => null,
                'featured_image' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Hand-finished unlined double-faced cashmere with tie sash belt.',
                'description' => 'A masterwork of artisanal tailoring. Features exaggerated notch lapels and an easy enveloping wrap silhouette.',
                'sizes' => ['XS', 'S', 'M', 'L'],
                'colors' => [
                    ['name' => 'Golden Camel', 'code' => '#C49E6C'],
                    ['name' => 'Alabaster', 'code' => '#EDE8DF'],
                ],
                'is_bestseller' => true,
            ],

            // WOMEN: Cashmere Knitwear
            [
                'name' => 'Featherweight Cashmere Crewneck',
                'category_slug' => 'women-knitwear',
                'price' => 260.00,
                'sale_price' => 220.00,
                'featured_image' => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Ultra-soft 100% Mongolian cashmere spun into a semi-sheer gossamer knit.',
                'description' => 'Designed with subtle dropped shoulders and ribbed trims. Perfect worn solo against skin or layered beneath tailored jackets.',
                'sizes' => ['XS', 'S', 'M', 'L'],
                'colors' => [
                    ['name' => 'Powder Blue', 'code' => '#B0C4DE'],
                    ['name' => 'Dusty Rose', 'code' => '#D8A47F'],
                    ['name' => 'Pure Ivory', 'code' => '#F8F6F0'],
                ],
                'is_bestseller' => true,
            ],
            [
                'name' => 'Chunky Ribbed Cashmere Turtleneck',
                'category_slug' => 'women-knitwear',
                'price' => 360.00,
                'sale_price' => null,
                'featured_image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Generously proportioned roll-neck sweater knitted from 4-ply cashmere.',
                'description' => 'Sumptuous texture with deep ribbed hems and an easy slouchy drape that balances beautifully over silk skirts.',
                'sizes' => ['S', 'M', 'L'],
                'colors' => [
                    ['name' => 'Oatmeal', 'code' => '#E5DFD5'],
                    ['name' => 'Chalk White', 'code' => '#F4F1EA'],
                ],
                'is_bestseller' => false,
            ],

            // WOMEN: Blouses & Silk Tops
            [
                'name' => 'Lavallière Tie-Neck Silk Chiffon Blouse',
                'category_slug' => 'women-tops',
                'price' => 280.00,
                'sale_price' => 245.00,
                'featured_image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Pure 16mm Mulberry silk crepe de chine with fluid cascading neck ties.',
                'description' => 'Features gathered shoulder yokes and mother-of-pearl buttons. Wear the neck scarf draped loose or tied in an elaborate bow.',
                'sizes' => ['XS', 'S', 'M', 'L'],
                'colors' => [
                    ['name' => 'Champagne', 'code' => '#F7E7CE'],
                    ['name' => 'Midnight Noir', 'code' => '#111111'],
                ],
                'is_bestseller' => true,
            ],
            [
                'name' => 'Poplin Mandarin Collar Minimalist Shirt',
                'category_slug' => 'women-tops',
                'price' => 190.00,
                'sale_price' => null,
                'featured_image' => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Crisp 100% organic cotton poplin tailored with clean French placket.',
                'description' => 'A timeless shirting silhouette with dropped armholes, elongated barrel cuffs, and a curved high-low hemline.',
                'sizes' => ['XS', 'S', 'M', 'L'],
                'colors' => [
                    ['name' => 'Crisp White', 'code' => '#FFFFFF'],
                    ['name' => 'French Blue Stripe', 'code' => '#6C7A89'],
                ],
                'is_bestseller' => false,
            ],

            // WOMEN: Skirts & Trousers
            [
                'name' => 'Bias-Cut Silk Charmeuse Maxi Skirt',
                'category_slug' => 'women-skirts',
                'price' => 240.00,
                'sale_price' => 210.00,
                'featured_image' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Heavyweight 22mm silk charmeuse cut on the bias for liquid drape.',
                'description' => 'Slips over hips effortlessly with an invisible elasticized waistband. Skims ankles with a soft, swaying hemline.',
                'sizes' => ['XS', 'S', 'M', 'L'],
                'colors' => [
                    ['name' => 'Golden Sand', 'code' => '#D8CCBC'],
                    ['name' => 'Dark Chocolate', 'code' => '#2B1B17'],
                ],
                'is_bestseller' => true,
            ],
            [
                'name' => 'High-Waisted Pleated Wool Palazzos',
                'category_slug' => 'women-skirts',
                'price' => 310.00,
                'sale_price' => null,
                'featured_image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Dramatic wide-leg trousers drafted with deep front double knife pleats.',
                'description' => 'Woven from supple Italian wool flannel with a natural drape. Pairs with minimalist knits or tailored jackets.',
                'sizes' => ['2', '4', '6', '8', '10'],
                'colors' => [
                    ['name' => 'Heather Taupe', 'code' => '#8B8589'],
                    ['name' => 'Pure Noir', 'code' => '#111111'],
                ],
                'is_bestseller' => false,
            ],

            // WOMEN: Silk & Evening Dresses (additional)
            [
                'name' => 'Plunge-Back Satin Column Gown',
                'category_slug' => 'women-dresses',
                'price' => 560.00,
                'sale_price' => 495.00,
                'featured_image' => 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Floor-sweeping silk satin evening gown with dramatic low cowl back.',
                'description' => 'An editorial red-carpet piece crafted from lustrous double-face satin. Features an integrated interior bustier and side-slit hem.',
                'sizes' => ['XS', 'S', 'M', 'L'],
                'colors' => [
                    ['name' => 'Onyx Noir', 'code' => '#111111'],
                    ['name' => 'Emerald Jewel', 'code' => '#0F4D32'],
                ],
                'is_bestseller' => true,
            ],

            // KIDS: Boys Tailoring & Shirts
            [
                'name' => 'Junior Tailored Linen Suit Blazer',
                'category_slug' => 'kids-boys',
                'price' => 145.00,
                'sale_price' => 125.00,
                'featured_image' => 'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Breathable pure European linen tailored jacket with mother-of-pearl buttons.',
                'description' => 'Handcrafted for formal celebrations and weddings. Lightweight, unlined construction ensures freedom of movement for active boys.',
                'sizes' => ['4Y', '6Y', '8Y', '10Y', '12Y'],
                'colors' => [
                    ['name' => 'Natural Sand', 'code' => '#D8CCBC'],
                    ['name' => 'Deep Navy', 'code' => '#1B263B'],
                ],
                'is_bestseller' => true,
            ],
            [
                'name' => 'Boys Oxford Grandad Collar Shirt',
                'category_slug' => 'kids-boys',
                'price' => 65.00,
                'sale_price' => null,
                'featured_image' => 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Woven from 100% organic cotton pin-point Oxford fabric.',
                'description' => 'A sharp band-collar shirt with curved hem and buttoned gauntlets, designed to look neat tucked in or worn loose.',
                'sizes' => ['4Y', '6Y', '8Y', '10Y', '12Y'],
                'colors' => [
                    ['name' => 'Sky Blue', 'code' => '#87CEEB'],
                    ['name' => 'Chalk White', 'code' => '#FFFFFF'],
                ],
                'is_bestseller' => false,
            ],

            // KIDS: Girls Dresses & Sets
            [
                'name' => 'Smocked Broderie Anglaise Cotton Dress',
                'category_slug' => 'kids-girls',
                'price' => 95.00,
                'sale_price' => 85.00,
                'featured_image' => 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Heirloom-quality cotton lawn dress with hand-smocked bodice and eyelet embroidery.',
                'description' => 'Delicate flutter sleeves and buttoned back placket, fully lined in breathable organic cotton voile.',
                'sizes' => ['4Y', '6Y', '8Y', '10Y'],
                'colors' => [
                    ['name' => 'Soft Petal Pink', 'code' => '#F4C2C2'],
                    ['name' => 'Pure Ivory', 'code' => '#FFFDF9'],
                ],
                'is_bestseller' => true,
            ],

            // KIDS: Everyday Essentials
            [
                'name' => 'Kids Organic Breton Stripe Longsleeve',
                'category_slug' => 'kids-essentials',
                'price' => 45.00,
                'sale_price' => null,
                'featured_image' => 'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Heavyweight 220 GSM combed organic cotton jersey with shoulder snaps.',
                'description' => 'A timeless nautical Breton stripe long-sleeve tee with reinforced neck ribs designed to withstand daily adventures and washings.',
                'sizes' => ['2Y', '4Y', '6Y', '8Y', '10Y'],
                'colors' => [
                    ['name' => 'Navy/Cream Stripe', 'code' => '#1B263B'],
                    ['name' => 'Olive/Cream Stripe', 'code' => '#556B2F'],
                ],
                'is_bestseller' => false,
            ],
            [
                'name' => 'Kids French Terry Relaxed Sweatpants',
                'category_slug' => 'kids-essentials',
                'price' => 50.00,
                'sale_price' => null,
                'featured_image' => 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Cozy brushed-back French terry joggers with natural cotton drawstring.',
                'description' => 'Built with elasticated ankle cuffs, side slip pockets, and reinforced flatlock stitching for maximum comfort and durability.',
                'sizes' => ['4Y', '6Y', '8Y', '10Y'],
                'colors' => [
                    ['name' => 'Oat Heather', 'code' => '#D8D0C5'],
                    ['name' => 'Washed Noir', 'code' => '#222222'],
                ],
                'is_bestseller' => false,
            ],

            // WINTER: Shearling & Leather
            [
                'name' => 'Spanish Merino Shearling Aviator Jacket',
                'category_slug' => 'winter-shearling',
                'price' => 1250.00,
                'sale_price' => 1100.00,
                'featured_image' => 'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1544022613-e87ca75a784a?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Heavyweight natural Spanish lamb shearling with antique brass buckle hardware.',
                'description' => 'The ultimate cold-weather investment coat. Featuring plush insulating wool interior, storm collar buckles, and dual hand warmer pockets.',
                'sizes' => ['38', '40', '42', '44'],
                'colors' => [
                    ['name' => 'Rich Chestnut', 'code' => '#582F0E'],
                    ['name' => 'Vintage Matte Black', 'code' => '#1A1A1A'],
                ],
                'is_bestseller' => true,
            ],
            [
                'name' => 'Buttery Nappa Leather Minimalist Trench',
                'category_slug' => 'winter-shearling',
                'price' => 980.00,
                'sale_price' => null,
                'featured_image' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Full-grain lambskin nappa with cupro lining and storm flap details.',
                'description' => 'Sleek, feather-soft leather tailored with clean lines, raglan sleeves, and a self-tie waist belt for modern runway silhouette.',
                'sizes' => ['S', 'M', 'L'],
                'colors' => [
                    ['name' => 'Dark Espresso', 'code' => '#2E1C14'],
                    ['name' => 'Midnight Noir', 'code' => '#111111'],
                ],
                'is_bestseller' => false,
            ],

            // WINTER: Scarves & Accessories (additional)
            [
                'name' => 'Oversized Brushed Mohair Plaid Scarf',
                'category_slug' => 'winter-accessories',
                'price' => 165.00,
                'sale_price' => 140.00,
                'featured_image' => 'https://images.unsplash.com/photo-1520903920243-00d872a2d1c9?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1544022613-e87ca75a784a?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Woven in Scotland from long-staple mohair and wool with long twisted fringe.',
                'description' => 'Generously proportioned at 220cm length for dramatic cocoon styling. Light, lofty, and exceptionally insulating.',
                'sizes' => ['One Size'],
                'colors' => [
                    ['name' => 'Caramel Ombré', 'code' => '#AF6E4D'],
                    ['name' => 'Muted Winter Tartan', 'code' => '#4A5B52'],
                ],
                'is_bestseller' => true,
            ],
            [
                'name' => 'Cashmere-Lined Deerskin Driving Gloves',
                'category_slug' => 'winter-accessories',
                'price' => 140.00,
                'sale_price' => null,
                'featured_image' => 'https://images.unsplash.com/photo-1544022613-e87ca75a784a?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1520903920243-00d872a2d1c9?auto=format&fit=crop&w=900&q=80',
                ],
                'short_description' => 'Supple grain deerskin leather hand-sewn with 100% cashmere knit lining.',
                'description' => 'Features conductive leather fingertips for smartphone touchscreens, palm keyholes, and elasticated wrist snaps.',
                'sizes' => ['S', 'M', 'L'],
                'colors' => [
                    ['name' => 'Cognac Tan', 'code' => '#9E5B32'],
                    ['name' => 'Matte Black', 'code' => '#111111'],
                ],
                'is_bestseller' => false,
            ],
        ];

        $count = 0;
        foreach ($catalog as $item) {
            $category = Category::where('slug', $item['category_slug'])->first();
            if (!$category) {
                $this->warn("Category slug {$item['category_slug']} not found, skipping {$item['name']}");
                continue;
            }

            $slug = Str::slug($item['name']);

            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $item['name'],
                    'sku' => 'SKU-' . strtoupper(Str::random(8)),
                    'category_id' => $category->id,
                    'price' => $item['price'],
                    'sale_price' => $item['sale_price'],
                    'stock' => 45,
                    'featured_image' => $item['featured_image'],
                    'gallery' => $item['gallery'] ?? [],
                    'short_description' => $item['short_description'],
                    'description' => $item['description'],
                    'is_featured' => true,
                    'is_bestseller' => $item['is_bestseller'] ?? false,
                    'is_new' => true,
                    'status' => 'published',
                ]
            );

            // Sync size & color variants
            $product->variants()->delete();

            $colors = $item['colors'] ?? [['name' => 'Standard', 'code' => '#111111']];
            $sizes = $item['sizes'] ?? ['Standard'];

            foreach ($colors as $color) {
                foreach ($sizes as $size) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $product->sku . '-' . strtoupper(Str::slug($color['name'])) . '-' . strtoupper($size),
                        'title' => "{$color['name']} / {$size}",
                        'color' => $color['name'],
                        'color_code' => $color['code'],
                        'size' => $size,
                        'price' => $product->price,
                        'compare_at_price' => $product->compare_at_price,
                        'stock' => 12,
                        'image' => $product->featured_image,
                        'is_active' => true,
                    ]);
                }
            }

            $count++;
            $this->line("✓ Populated [{$category->name}] -> <info>{$product->name}</info> (\${$product->price})");
        }

        $this->info("Successfully populated {$count} luxury garments across all categories!");
        return 0;
    }
}
