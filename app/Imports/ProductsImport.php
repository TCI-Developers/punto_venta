<?php

namespace App\Imports;

use App\Models\{Product, PartToProduct, UnidadSat};
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{ToModel, ToCollection};
use Illuminate\Support\Facades\{Log};

class ProductsImport implements ToCollection
{
    // public function collection(Collection $rows)
    // {   

    //     $allProducts = Product::all()->keyBy('code_product');
    //     $allUnits = UnidadSat::all()->keyBy('clave_unidad');
    //     $allParts = PartToProduct::all()->keyBy('code_bar');

    //     $partToProductToInsert = [];

    //     foreach ($rows as $index => $row) {
    //         $code_product = $row[0]; //codigo producto      
    //         $stock = $row[4];

    //         if($index > 1){
    //             $product = $allProducts->get($code_product);
    //             // $product = Product::where('code_product', $code_product)->first();
    //             if(is_object($product)){
    //                 $positions = $this->getPositions($rows, $code_product);
    //                 if(count($positions)){
    //                     foreach($positions as $item){
    //                         $code_bar = (string) $rows[$item][6];
    //                         $code_bar = strtok($code_bar, '.');
    //                         $equivalencia = $rows[$item][7];

    //                         // $unit_sat = UnidadSat::where('clave_unidad', $product->unit)->first();
    //                         // $part_to_product = PartToProduct::where('code_bar', $code_bar)->first();
    //                         $unit_sat = $allUnits->get($product->unit ?? '');
    //                         $part_to_product = $allParts->get($code_bar);

    //                         $cantidad_despiece = 0;
    //                         if($equivalencia > 1 || $equivalencia < 1){
    //                             $cantidad_despiece = $equivalencia < 1 ? (1/$equivalencia) : (100/$equivalencia);
    //                         }

    //                         if(!is_object($part_to_product)){
    //                             $part_to_product = new PartToProduct();
    //                             $part_to_product->product_id = $product->id;
    //                         }
                            
    //                         if($cantidad_despiece > 0){
    //                             $part_to_product->price = ($product->precio_despiece/$cantidad_despiece);
    //                             $part_to_product->cantidad_despiezado = $cantidad_despiece;
    //                         }else{
    //                             $part_to_product->price = $product->precio;
    //                         }
                            
    //                         $part_to_product->price_mayoreo = $product->precio_mayoreo;
    //                         $part_to_product->code_bar = $code_bar;
    //                         $part_to_product->unidad_sat_id = $unit_sat->id;
    //                         $part_to_product->save();
    //                     }
    //                 }
    //                 $product->existence = $stock >= 1 ? $stock:0;
    //                 $product->save();
    //             }  
    //         }
    //     }
    // }

    public function collection(Collection $rows)
    {   
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $allProducts = Product::all()->keyBy('code_product');
        $allUnits = UnidadSat::all()->keyBy('clave_unidad');
        $existingParts = PartToProduct::all()->keyBy('code_bar');

        $partToProductToInsert = [];

        try {
            foreach ($rows as $index => $row) {
                if ($index <= 1) continue;

                $code_product = $row[0];
                $stock = $row[4];

                $product = $allProducts->get($code_product);
                if (!$product) continue;

                $positions = $this->getPositions($rows, $code_product);
                if (!count($positions)) continue;

                foreach ($positions as $item) {
                    $code_bar = (string) $rows[$item][6];
                    $code_bar = strtok($code_bar, '.');
                    $equivalencia = $rows[$item][7];

                    if (!$code_bar) continue;

                    $unit_sat = $allUnits->get($product->unit);
                    if (!$unit_sat) continue;

                    $cantidad_despiece = 0;
                    if ($equivalencia > 1 || $equivalencia < 1) {
                        $cantidad_despiece = $equivalencia < 1 ? (1 / $equivalencia) : (100 / $equivalencia);
                    }

                    $part_to_product = $existingParts->get($code_bar);
                    if (!$part_to_product) {
                        $part_to_product = new PartToProduct();
                        $part_to_product->product_id = $product->id;
                        $part_to_product->code_bar = $code_bar;
                    }

                    $part_to_product->unidad_sat_id = $unit_sat->id;
                    $part_to_product->price_mayoreo = $product->precio_mayoreo;

                    if ($cantidad_despiece > 0) {
                        $part_to_product->price = ($product->precio_despiece / $cantidad_despiece);
                        $part_to_product->cantidad_despiezado = $cantidad_despiece;
                    } else {
                        $part_to_product->price = $product->precio;
                    }

                    $partToProductToInsert[] = $part_to_product;
                }

                $product->existence = $stock >= 1 ? $stock : 0;
                $product->save();
            }

            // Guardar todos los PartToProduct en lote
            foreach (array_chunk($partToProductToInsert, 500) as $batch) {
                $now = now();
                PartToProduct::insert(
                    collect($batch)->map(function ($item) use ($now) {
                        $attributes = $item->getAttributes();
                        $attributes['created_at'] = $now;
                        $attributes['updated_at'] = $now;
                        return $attributes;
                    })->toArray()
                );
            }
        } catch (\Throwable $th) {
            Log::error('Error al procesar excel: '. $th->getMessage());
            return redirect()->back();
        }
    }

    //funcion para obtener las posiciones de las equivalencias y codigos de barras
    private function getPositions($rows, $code_product){
        $contador = 0;
        foreach($rows as $index => $item){
            $code_product_match = $item[5];
            if($code_product_match == $code_product){
                $positions[] = $index;
                $contador++;
            }

            if($contador == 100){
                break;
            }
        }

        return $positions ?? []; 
    }
    
}
