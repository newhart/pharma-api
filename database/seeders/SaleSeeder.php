<?php

namespace Database\Seeders;

use App\Models\Sale;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sales = Sale::all();
        foreach ($sales as $sale) {
            if ($sale->saleStay === 0.0) {
                $sale->stateSale = 'Valider';
                $sale->created_at = now();
                $sale->user_id = 1;
                $sale->save();
            }
        }
    }
}
