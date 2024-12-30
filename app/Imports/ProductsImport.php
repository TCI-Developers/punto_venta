<?php

namespace App\Imports;

use App\Models\{Product, PartToProduct};
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{ToModel, ToCollection};

class ProductsImport implements ToCollection
{
    public function collection(Collection $rows)
    {   
        // dd($rows);
        foreach ($rows as $index => $row) {
            $code_product = $row[0];
            $barcode = $row[1];
            $price = $row[2];
            $stock = $row[3];
            
            if($index > 0){
                $part_to_product = PartToProduct::where('code_bar', $barcode)->first();
                if(is_object($part_to_product)){
                    $part_to_product->price = $price;
                    $part_to_product->stock = $stock;
                    $part_to_product->save();
                }
            }
        }
    }
}
