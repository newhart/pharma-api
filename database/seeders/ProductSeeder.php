<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = storage_path('app/products.csv');
        $handle = fopen($csvFile, "r");

        if ($handle !== false) {
            $header = fgetcsv($handle);

            while (($data = fgetcsv($handle)) !== false) {
                $row = array_combine($header, $data);
                // Define the new key names
                $newKeys = [
                    'reference',
                    'name',
                    'quantityGellule',
                    'quantityPlaquette',
                    'quantityBoite',
                    'priceGellule',
                    'pricePlaquette',
                    'priceBoite',
                    'datePeremption',
                    'libele'
                ];
                // Retrieve the array values
                $values = array_values($row);
                // Combine the new keys with the values
                $datas = array_combine($newKeys, $values);
                $datas = $this->generate_data($datas);
                if ($row !== false) {
                    Product::create($datas);
                }
            }

            fclose($handle);
        }
    }

    private function formate_date(string $dateString)
    {
        $date = \DateTime::createFromFormat('d/m/Y', $dateString);

        $formattedDate = $date->format('Y-m-d');

        return $formattedDate;
    }

    private function generate_data(array $data): array
    {
        $data['quantityGellule'] = (int) $data['quantityGellule'] ?? 0;
        $data['quantityPlaquette'] = (int) $data['quantityPlaquette'] ?? 0;
        $data['quantityBoite'] = (int) $data['quantityBoite'] ?? 0;
        $data['quantityGellule'] = (int) $data['quantityGellule'] ?? 0;
        $data['priceGellule'] = $this->change_date_type($data['priceGellule']);
        $data['pricePlaquette'] = $this->change_date_type($data['pricePlaquette']);
        $data['priceBoite'] = $this->change_date_type($data['priceBoite']);
        $data['datePeremption'] = $data['datePeremption'] === '' ?  null : $this->formate_date($data['datePeremption']);
        return $data;
    }

    private function change_date_type(string $string): float
    {
        $search = [",", "Ar"];
        $replace = [".", ""];
        return (float) str_replace($search, $replace, $string);
    }
}
