<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $products = [
            'textured-knit-sweater' => ['Textured knit sweater', 'To‘qimali trikotaj sviter', 'Мягкий фактурный трикотажный свитер — удобный и элегантный выбор на каждый день.', 'A soft textured knit sweater, an easy and elegant choice for everyday wear.'],
            'classic-denim-jacket' => ['Classic denim jacket', 'Klassik denim kurtka', 'Классическая джинсовая куртка, которая подходит для любого сезона.', 'A classic denim jacket made for every season.'],
            'everyday-leather-bag' => ['Everyday leather bag', 'Kundalik charm sumka', 'Компактная сумка из искусственной кожи для повседневного использования.', 'A compact faux leather bag for everyday use.'],
            'relaxed-cotton-shirt' => ['Relaxed cotton shirt', 'Erkin bichimli paxta ko‘ylak', 'Свободная рубашка из лёгкого хлопка.', 'A relaxed fit shirt made from lightweight cotton.'],
            'soft-everyday-hoodie' => ['Soft everyday hoodie', 'Yumshoq kundalik xudi', 'Мягкая ткань и свободный крой для комфорта каждый день.', 'Soft fabric and an easy fit for everyday comfort.'],
            'minimal-leather-wallet' => ['Minimal leather wallet', 'Minimal charm hamyon', 'Компактный кошелёк для карт и других повседневных мелочей.', 'A compact wallet for cards and everyday essentials.'],
            'structured-shoulder-bag' => ['Structured shoulder bag', 'Shaklli yelka sumkasi', 'Сумка лаконичной формы для повседневных и нарядных образов.', 'A clean-lined bag for both everyday and occasion looks.'],
            'modern-essential-jacket' => ['Modern essential jacket', 'Zamonaviy asosiy kurtka', 'Современный силуэт и удобный вариант для многослойных образов.', 'A modern shape and a comfortable layer for everyday outfits.'],
        ];

        foreach ($products as $slug => [$oldName, $uzName, $ruDescription, $enDescription]) {
            $product = DB::table('products')->where('slug', $slug)->first();
            if (!$product || $product->name !== $oldName) {
                continue;
            }

            $translations = json_decode((string) $product->translations, true) ?: [];
            $translations['ru']['description'] ??= $ruDescription;
            $translations['en']['description'] ??= $enDescription;

            DB::table('products')->where('id', $product->id)->update([
                'name' => $uzName,
                'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Product edits made after this migration should never be reverted automatically.
    }
};
