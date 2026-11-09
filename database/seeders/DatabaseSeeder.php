<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email'=>'admin@vermontshop.local'],['name'=>'Vermontshop Admin','password'=>'ChangeMe123!','role'=>'admin','email_verified'=>true,'email_verified_at'=>now()]);
        $products = [
            ['name'=>'To‘qimali trikotaj sviter','slug'=>'textured-knit-sweater','category'=>'Kiyim','gender'=>'women','description'=>'Yumshoq teksturali trikotaj sviter. Kundalik obraz uchun qulay va nafis tanlov.','price'=>190000,'old_price'=>240000,'image_url'=>'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=900&q=85','colors'=>['Lavender','Cream','Blue'],'sizes'=>['S','M','L'],'stock'=>12,'featured'=>true],
            ['name'=>'Klassik denim kurtka','slug'=>'classic-denim-jacket','category'=>'Kurtka','gender'=>'men','description'=>'Har mavsumga mos klassik denim kurtka.','price'=>420000,'old_price'=>null,'image_url'=>'https://images.unsplash.com/photo-1516257984-b1b4d707412e?auto=format&fit=crop&w=900&q=85','colors'=>['Blue','Black'],'sizes'=>['M','L','XL'],'stock'=>8,'featured'=>true],
            ['name'=>'Kundalik charm sumka','slug'=>'everyday-leather-bag','category'=>'Aksessuar','gender'=>'women','description'=>'Kundalik foydalanishga mos ixcham sumka.','price'=>310000,'old_price'=>350000,'image_url'=>'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=900&q=85','colors'=>['Black','Brown'],'sizes'=>['One size'],'stock'=>10,'featured'=>true],
            ['name'=>'Erkin bichimli paxta ko‘ylak','slug'=>'relaxed-cotton-shirt','category'=>'Kiyim','gender'=>'men','description'=>'Yengil paxtali matodan tikilgan keng bichimli ko‘ylak.','price'=>260000,'old_price'=>null,'image_url'=>'https://images.unsplash.com/photo-1603252109303-2751441dd157?auto=format&fit=crop&w=900&q=85','colors'=>['White','Sand'],'sizes'=>['S','M','L','XL'],'stock'=>14,'featured'=>false],
            ['name'=>'Yumshoq kundalik xudi','slug'=>'soft-everyday-hoodie','category'=>'Kiyim','gender'=>'women','description'=>'Yumshoq mato va erkin bichim — har kun uchun.','price'=>280000,'old_price'=>320000,'image_url'=>'https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&w=900&q=85','colors'=>['Grey','Lilac'],'sizes'=>['S','M','L'],'stock'=>9,'featured'=>false],
            ['name'=>'Minimal charm hamyon','slug'=>'minimal-leather-wallet','category'=>'Aksessuar','gender'=>'men','description'=>'Ixcham dizaynli hamyon, kartalar va kundalik zarur narsalar uchun.','price'=>175000,'old_price'=>null,'image_url'=>'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=900&q=85','colors'=>['Black','Brown'],'sizes'=>['One size'],'stock'=>16,'featured'=>false],
            ['name'=>'Shaklli yelka sumkasi','slug'=>'structured-shoulder-bag','category'=>'Aksessuar','gender'=>'women','description'=>'Toza chiziqli, kundalik va bayramona libosga mos sumka.','price'=>360000,'old_price'=>null,'image_url'=>'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=900&q=85','colors'=>['Black','Beige'],'sizes'=>['One size'],'stock'=>7,'featured'=>false],
            ['name'=>'Zamonaviy asosiy kurtka','slug'=>'modern-essential-jacket','category'=>'Kurtka','gender'=>'men','description'=>'Zamonaviy shakl va qulay qatlamli kiyinish uchun kurtka.','price'=>510000,'old_price'=>570000,'image_url'=>'https://images.unsplash.com/photo-1544923246-77307dd654cb?auto=format&fit=crop&w=900&q=85','colors'=>['Black','Olive'],'sizes'=>['M','L','XL'],'stock'=>6,'featured'=>true],
        ];
        $names = [
            'textured-knit-sweater'=>['ru'=>'Фактурный вязаный свитер','en'=>'Textured knit sweater'],
            'classic-denim-jacket'=>['ru'=>'Классическая джинсовая куртка','en'=>'Classic denim jacket'],
            'everyday-leather-bag'=>['ru'=>'Кожаная сумка на каждый день','en'=>'Everyday leather bag'],
            'relaxed-cotton-shirt'=>['ru'=>'Свободная хлопковая рубашка','en'=>'Relaxed cotton shirt'],
            'soft-everyday-hoodie'=>['ru'=>'Мягкое худи на каждый день','en'=>'Soft everyday hoodie'],
            'minimal-leather-wallet'=>['ru'=>'Минималистичный кожаный кошелёк','en'=>'Minimal leather wallet'],
            'structured-shoulder-bag'=>['ru'=>'Структурированная сумка на плечо','en'=>'Structured shoulder bag'],
            'modern-essential-jacket'=>['ru'=>'Современная базовая куртка','en'=>'Modern essential jacket'],
        ];
        $descriptions = [
            'textured-knit-sweater'=>['ru'=>'Мягкий фактурный трикотажный свитер — удобный и элегантный выбор на каждый день.','en'=>'A soft textured knit sweater, an easy and elegant choice for everyday wear.'],
            'classic-denim-jacket'=>['ru'=>'Классическая джинсовая куртка, которая подходит для любого сезона.','en'=>'A classic denim jacket made for every season.'],
            'everyday-leather-bag'=>['ru'=>'Компактная сумка из искусственной кожи для повседневного использования.','en'=>'A compact faux leather bag for everyday use.'],
            'relaxed-cotton-shirt'=>['ru'=>'Свободная рубашка из лёгкого хлопка.','en'=>'A relaxed fit shirt made from lightweight cotton.'],
            'soft-everyday-hoodie'=>['ru'=>'Мягкая ткань и свободный крой для комфорта каждый день.','en'=>'Soft fabric and an easy fit for everyday comfort.'],
            'minimal-leather-wallet'=>['ru'=>'Компактный кошелёк для карт и других повседневных мелочей.','en'=>'A compact wallet for cards and everyday essentials.'],
            'structured-shoulder-bag'=>['ru'=>'Сумка лаконичной формы для повседневных и нарядных образов.','en'=>'A clean-lined bag for both everyday and occasion looks.'],
            'modern-essential-jacket'=>['ru'=>'Современный силуэт и удобный вариант для многослойных образов.','en'=>'A modern shape and a comfortable layer for everyday outfits.'],
        ];
        foreach ($products as $product) {
            Category::firstOrCreate(['name' => $product['category']]);
            $slug = $product['slug'];
            $product['translations']=[
                'ru'=>['name'=>$names[$slug]['ru'],'description'=>$descriptions[$slug]['ru']],
                'en'=>['name'=>$names[$slug]['en'],'description'=>$descriptions[$slug]['en']],
            ];
            Product::updateOrCreate(['slug'=>$product['slug']],$product);
        }
    }
}
