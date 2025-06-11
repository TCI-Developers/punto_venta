<?php

namespace App\Imports;

use App\Models\{Product, PartToProduct, UnidadSat};
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{ToModel, ToCollection};

class ProductsImport implements ToCollection
{
    public function collection(Collection $rows)
    {   
        foreach ($rows as $index => $row) {
            $code_product = $row[0];
            $stock = $row[1];
            $code_bar = $row[2];

            if($index > 0){
                $product = Product::where('code_product', $code_product)->first();
                if(is_object($product)){
                    $unit_sat = UnidadSat::where('clave_unidad', $product->unit)->first();
                    $part_to_product = PartToProduct::where('code_bar', $code_bar)->first();

                    if(!is_object($part_to_product)){
                        $part_to_product = new PartToProduct();
                        $part_to_product->product_id = $product->id;
                        $part_to_product->price = $product->precio;
                        $part_to_product->code_bar = $code_bar;
                        $part_to_product->stock = $stock;
                        $part_to_product->unidad_sat_id = $unit_sat->id;
                        $part_to_product->save();

                        $product->existence = $stock;
                        $product->save();
                    }
                }
            }
        }
    }
}
