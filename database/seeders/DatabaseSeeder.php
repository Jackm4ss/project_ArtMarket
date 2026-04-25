<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'seller', 'user'] as $role) {
            Role::findOrCreate($role);
        }

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@artmarket.test'],
            [
                'name' => 'Admin Art Market',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        $sellerUser = User::query()->firstOrCreate(
            ['email' => 'seller@artmarket.test'],
            [
                'name' => 'Seller Art Market',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $sellerUser->assignRole('seller');

        $buyer = User::query()->firstOrCreate(
            ['email' => 'user@artmarket.test'],
            [
                'name' => 'User Art Market',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $buyer->assignRole('user');

        $seller = Seller::query()->firstOrCreate(
            ['user_id' => $sellerUser->id],
            [
                'store_name' => 'Nusantara Art Studio',
                'bio' => 'Studio seni kurasi karya kontemporer Indonesia.',
                'status' => 'active',
                'location' => 'Yogyakarta',
                'phone' => '081234567890',
                'bank_name' => 'BCA',
                'bank_account_name' => 'Nusantara Art Studio',
                'bank_account_number' => '1234567890',
                'rating_average' => 4.9,
                'rating_count' => 128,
                'verified_at' => now(),
            ]
        );

        $categories = collect([
            ['name' => 'Lukisan', 'description' => 'Karya dua dimensi dari seniman Indonesia.', 'sort_order' => 1],
            ['name' => 'Patung', 'description' => 'Karya tiga dimensi untuk ruang koleksi.', 'sort_order' => 2],
            ['name' => 'Relief', 'description' => 'Karya tekstural dengan narasi visual kuat.', 'sort_order' => 3],
            ['name' => 'Kerajinan Seni', 'description' => 'Objek artistik hasil keterampilan tangan.', 'sort_order' => 4],
            ['name' => 'Dekorasi Artistik', 'description' => 'Aksen visual untuk rumah dan ruang komersial.', 'sort_order' => 5],
        ])->mapWithKeys(function (array $category): array {
            $model = Category::query()->firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                $category + ['is_active' => true]
            );

            return [$category['name'] => $model];
        });

        $products = [
            [
                'sku' => 'ART-LKS-001',
                'title' => 'Kebun Cahaya Tropis',
                'category' => 'Lukisan',
                'price' => 2450000,
                'stock' => 8,
                'material' => 'Akrilik di atas kanvas',
                'dimensions' => '80 x 100 cm',
                'location' => 'Yogyakarta',
                'sold_count' => 42,
            ],
            [
                'sku' => 'ART-PTG-001',
                'title' => 'Patung Senyap Kayu Jati',
                'category' => 'Patung',
                'price' => 5200000,
                'stock' => 3,
                'material' => 'Kayu jati finishing natural',
                'dimensions' => '35 x 35 x 90 cm',
                'location' => 'Jepara',
                'sold_count' => 19,
            ],
            [
                'sku' => 'ART-RLF-001',
                'title' => 'Relief Jejak Pesisir',
                'category' => 'Relief',
                'price' => 3800000,
                'stock' => 5,
                'material' => 'Mixed media dan pigmen alam',
                'dimensions' => '100 x 60 cm',
                'location' => 'Bali',
                'sold_count' => 28,
            ],
            [
                'sku' => 'ART-KRJ-001',
                'title' => 'Anyaman Rupa Tanah',
                'category' => 'Kerajinan Seni',
                'price' => 950000,
                'stock' => 15,
                'material' => 'Serat alami dan rotan',
                'dimensions' => '45 x 45 cm',
                'location' => 'Lombok',
                'sold_count' => 75,
            ],
            [
                'sku' => 'ART-DKR-001',
                'title' => 'Dekorasi Dinding Rimba',
                'category' => 'Dekorasi Artistik',
                'price' => 1350000,
                'stock' => 10,
                'material' => 'Kayu, tekstil, dan cat mineral',
                'dimensions' => '70 x 90 cm',
                'location' => 'Bandung',
                'sold_count' => 64,
            ],
            [
                'sku' => 'ART-LKS-002',
                'title' => 'Langit Emas Setelah Hujan',
                'category' => 'Lukisan',
                'price' => 7100000,
                'stock' => 2,
                'material' => 'Oil pastel dan akrilik',
                'dimensions' => '120 x 160 cm',
                'location' => 'Jakarta',
                'sold_count' => 11,
            ],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'seller_id' => $seller->id,
                    'category_id' => $categories[$product['category']]->id,
                    'title' => $product['title'],
                    'slug' => Str::slug($product['title']),
                    'excerpt' => 'Karya pilihan dengan kurasi editorial Art Market.',
                    'description' => 'Karya ini dipilih untuk kolektor yang mencari ekspresi visual autentik, material berkualitas, dan cerita yang kuat dari seniman Indonesia.',
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'status' => ProductStatus::Published,
                    'product_type' => 'ready',
                    'material' => $product['material'],
                    'dimensions' => $product['dimensions'],
                    'weight_gram' => 2500,
                    'location' => $product['location'],
                    'is_featured' => true,
                    'views_count' => 0,
                    'sold_count' => $product['sold_count'],
                    'rating_average' => 4.8,
                    'rating_count' => 24,
                    'published_at' => now(),
                ]
            );
        }
    }
}
