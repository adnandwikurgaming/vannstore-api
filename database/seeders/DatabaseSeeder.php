<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Data contoh yang sama untuk semua anggota dan tim Flutter.
 * Semua akun memakai password: password
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin VannStore',
            'email' => 'admin@vannstore.test',
        ]);

        $rajutRina = User::factory()->penjual()->create([
            'name' => 'Rina Rahayu',
            'email' => 'rina@vannstore.test',
            'store_name' => 'Rajut Rina',
        ]);

        $kayuBagas = User::factory()->penjual()->create([
            'name' => 'Bagas Pratama',
            'email' => 'bagas@vannstore.test',
            'store_name' => 'Kayu Bagas Workshop',
        ]);

        User::factory()->pembeli()->create([
            'name' => 'Andi Saputra',
            'email' => 'andi@vannstore.test',
        ]);

        User::factory()->pembeli()->create([
            'name' => 'Sari Wulandari',
            'email' => 'sari@vannstore.test',
        ]);

        $categories = collect(['Rajutan', 'Anyaman', 'Kerajinan Kayu', 'Perhiasan', 'Lukisan'])
            ->mapWithKeys(fn (string $name) => [
                $name => Category::create(['name' => $name, 'slug' => Str::slug($name)]),
            ]);

        $products = [
            [$rajutRina, 'Rajutan', 'Tas Rajut Totebag', 'Tas rajut benang katun, muat laptop 14 inci.', 185000, 8],
            [$rajutRina, 'Rajutan', 'Syal Rajut Wol', 'Syal wol lembut, panjang 160 cm.', 95000, 15],
            [$rajutRina, 'Rajutan', 'Boneka Amigurumi Kucing', 'Boneka rajut tinggi 20 cm, isian dakron.', 120000, 5],
            [$rajutRina, 'Anyaman', 'Keranjang Anyaman Pandan', 'Keranjang serbaguna diameter 30 cm.', 75000, 12],
            [$rajutRina, 'Perhiasan', 'Gelang Manik Etnik', 'Gelang manik kaca dengan motif etnik.', 45000, 1],
            [$kayuBagas, 'Kerajinan Kayu', 'Talenan Kayu Jati', 'Talenan jati 35x25 cm, finishing food grade.', 110000, 10],
            [$kayuBagas, 'Kerajinan Kayu', 'Jam Dinding Kayu Minimalis', 'Jam dinding kayu mahoni diameter 30 cm.', 230000, 4],
            [$kayuBagas, 'Kerajinan Kayu', 'Miniatur Vespa Kayu', 'Miniatur pajangan skala 1:12.', 175000, 0],
            [$kayuBagas, 'Anyaman', 'Tikar Anyaman Rotan', 'Tikar rotan 120x180 cm.', 320000, 3],
            [$kayuBagas, 'Lukisan', 'Lukisan Kanvas Sawah', 'Lukisan cat akrilik 40x60 cm.', 450000, 2],
        ];

        foreach ($products as [$seller, $category, $name, $description, $price, $stock]) {
            Product::create([
                'seller_id' => $seller->id,
                'category_id' => $categories[$category]->id,
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'stock' => $stock,
            ]);
        }
    }
}
