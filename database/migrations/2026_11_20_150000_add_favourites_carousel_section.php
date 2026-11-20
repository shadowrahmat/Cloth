<?php

use App\Models\HomepageSection;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the 3D "এই সপ্তাহের পছন্দ" product carousel right after the category
 * grid. Later sections move down one place; rollback removes it and moves
 * them back.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (HomepageSection::where('type', 'favourites_carousel')->exists()) {
            return;
        }

        $after = HomepageSection::where('type', 'category_grid')->value('display_order')
            ?? HomepageSection::where('type', 'hero')->value('display_order')
            ?? 0;

        HomepageSection::where('display_order', '>', $after)->increment('display_order');

        HomepageSection::create([
            'type' => 'favourites_carousel',
            'title' => 'এই সপ্তাহের পছন্দ (৩ডি ক্যারোসেল)',
            'display_order' => $after + 1,
            'is_active' => true,
            'content' => [
                'eyebrow' => 'সদ্য এসেছে',
                'heading' => 'এই সপ্তাহের পছন্দ',
                'highlight' => 'পছন্দ',   // this word is coloured with the brand gold
                'source' => 'new',        // new | bestseller | sale | collection:{slug} | category:{slug}
                'limit' => 8,             // 5–12
            ],
        ]);
    }

    public function down(): void
    {
        $section = HomepageSection::where('type', 'favourites_carousel')->first();
        if (! $section) {
            return;
        }
        $order = $section->display_order;
        $section->delete();
        HomepageSection::where('display_order', '>', $order)->decrement('display_order');
    }
};
