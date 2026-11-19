<?php

use App\Models\HomepageSection;
use Illuminate\Database\Migrations\Migration;

/**
 * Replace the "উইন্টার লুকবুক" (shop_the_look) homepage section with the
 * scroll-driven category showcase, in the same position. The old content is
 * kept inside the row so `migrate:rollback` restores it exactly.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (HomepageSection::where('type', 'shop_the_look')->get() as $section) {
            $section->update([
                'type' => 'category_showcase',
                'title' => 'ক্যাটাগরি শোকেস',
                'content' => [
                    'kicker' => 'ক্যাটাগরি অনুযায়ী দেখুন',
                    'heading' => 'আপনার স্টাইল বেছে নিন',
                    'description' => 'স্ক্রল করুন — প্রতিটি ক্যাটাগরির সেরা পোশাক এক নজরে।',
                    'categories' => [],            // empty = top categories by product count
                    'products_per_category' => 3,
                    '_previous' => ['title' => $section->title, 'subtitle' => $section->subtitle, 'content' => $section->content],
                ],
            ]);
        }
    }

    public function down(): void
    {
        foreach (HomepageSection::where('type', 'category_showcase')->get() as $section) {
            $previous = $section->content['_previous'] ?? null;
            if (! $previous) {
                continue;
            }
            $section->update([
                'type' => 'shop_the_look',
                'title' => $previous['title'] ?? $section->title,
                'subtitle' => $previous['subtitle'] ?? null,
                'content' => $previous['content'] ?? [],
            ]);
        }
    }
};
