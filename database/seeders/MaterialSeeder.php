<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $unitKg = DB::table('units')->where('abbreviation', 'kg')->first();
        $unitM2 = DB::table('units')->where('abbreviation', 'm²')->first();
        $unitM3 = DB::table('units')->where('abbreviation', 'm³')->first();
        $unitL = DB::table('units')->where('abbreviation', 'l')->first();
        $unitUn = DB::table('units')->where('abbreviation', 'un')->first();
        $unitML = DB::table('units')->where('abbreviation', 'ml')->first();

        $materials = [
            ['name' => 'Cimento', 'unit_id' => $unitKg->id, 'stock_quantity' => 500],
            ['name' => 'Areia Fina', 'unit_id' => $unitM3->id, 'stock_quantity' => 50],
            ['name' => 'Brita', 'unit_id' => $unitM3->id, 'stock_quantity' => 75],
            ['name' => 'Asfalto', 'unit_id' => $unitKg->id, 'stock_quantity' => 1000],
            ['name' => 'Tinta Branca', 'unit_id' => $unitL->id, 'stock_quantity' => 100],
            ['name' => 'Tinta Vermelha', 'unit_id' => $unitL->id, 'stock_quantity' => 50],
            ['name' => 'Tubo de PVC', 'unit_id' => $unitML->id, 'stock_quantity' => 200],
            ['name' => 'Condutor Elétrico', 'unit_id' => $unitML->id, 'stock_quantity' => 500],
            ['name' => 'Placas de Betão', 'unit_id' => $unitM2->id, 'stock_quantity' => 1000],
            ['name' => 'Calçada', 'unit_id' => $unitM2->id, 'stock_quantity' => 200],
            ['name' => 'Borracha', 'unit_id' => $unitKg->id, 'stock_quantity' => 100],
            ['name' => 'Parafusos Inox', 'unit_id' => $unitKg->id, 'stock_quantity' => 50],
            ['name' => 'Chapa Metálica', 'unit_id' => $unitM2->id, 'stock_quantity' => 30],
            ['name' => 'Adesivo de Betão', 'unit_id' => $unitL->id, 'stock_quantity' => 25],
            ['name' => 'Verniz Protetor', 'unit_id' => $unitL->id, 'stock_quantity' => 40],
        ];

        foreach ($materials as $material) {
            DB::table('materials')->insert([
                'id' => Str::uuid(),
                'name' => $material['name'],
                'unit_id' => $material['unit_id'],
                'stock_quantity' => $material['stock_quantity'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
