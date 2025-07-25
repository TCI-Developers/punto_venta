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
            $code_product = $row[0]; //codigo producto      
            $stock = $row[4];

            if($index > 1){
                $product = Product::where('code_product', $code_product)->first();
                if(is_object($product)){
                    $positions = $this->getPositions($rows, $code_product);
                    if(count($positions)){
                        foreach($positions as $item){
                            $code_bar = (string) $rows[$item][6];
                            $code_bar = strtok($code_bar, '.');
                            $equivalencia = $rows[$item][7];

                            $unit_sat = UnidadSat::where('clave_unidad', $product->unit)->first();
                            $part_to_product = PartToProduct::where('code_bar', $code_bar)->first();

                            $cantidad_despiece = 0;
                            if($equivalencia > 1 || $equivalencia < 1){
                                $cantidad_despiece = $equivalencia < 1 ? (1/$equivalencia) : (100/$equivalencia);
                            }

                            if(!is_object($part_to_product)){
                                $part_to_product = new PartToProduct();
                                $part_to_product->product_id = $product->id;
                            }
                            
                            if($cantidad_despiece > 0){
                                $part_to_product->price = ($product->precio_despiece/$cantidad_despiece);
                                $part_to_product->cantidad_despiezado = $cantidad_despiece;
                            }else{
                                $part_to_product->price = $product->precio;
                            }
                            
                            $part_to_product->price_mayoreo = $product->precio_mayoreo;
                            $part_to_product->code_bar = $code_bar;
                            $part_to_product->unidad_sat_id = $unit_sat->id;
                            $part_to_product->save();
                        }
                    }
                    $product->existence = $stock >= 1 ? $stock:0;
                    $product->save();
                }  
            }
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
