<?php

namespace App\Imports;

use App\Models\{Product, PartToProduct, UnidadSat};
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{ToModel, ToCollection, WithMultipleSheets};
use Illuminate\Support\Facades\{DB, Log};

class ProductsImport implements ToCollection, WithMultipleSheets
{
    public int $matched = 0;
    public int $skipped = 0;
    public array $skippedCodes = [];

    public function collection_old(Collection $rows)
    {   
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $allProducts = Product::all()->keyBy('code_product');
        $allUnits = UnidadSat::all()->keyBy('clave_unidad');
        $existingParts = PartToProduct::all()->keyBy('code_bar');

        $partToProductToInsert = [];

        // try {
        
            foreach ($rows as $index => $row) {
                if ($index <= 1) continue;
                $code_product = $row[0];
                $stock = $row[4];

                $product = $allProducts->get($code_product);
                if (!$product) continue;

                $positions = $this->getPositions($rows, $code_product);
                if (count($positions)){
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
                        // $part_to_product->save();
                        $partToProductToInsert[] = $part_to_product;
                        // dd($partToProductToInsert);
                    }
                }
                else{
                    $part_to_product = PartToProduct::where('product_id', $product->id)->first();
                    $unit_sat = $allUnits->get($product->unit);
                    if(!is_object($part_to_product)){
                        if (!$unit_sat) continue;

                        $part_to_product = new PartToProduct();
                        $part_to_product->product_id = $product->id;
                        $part_to_product->code_bar = 'N/A';
                    }

                    $part_to_product->unidad_sat_id = $unit_sat->id;
                    $part_to_product->price_mayoreo = $product->precio_mayoreo;
                    $part_to_product->price = $product->precio;
                    // $part_to_product->save();
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
        // } catch (\Throwable $th) {
        //     Log::error('Error al procesar excel: '. $th->getMessage());
        //     return redirect()->back();
        // }
    }

    //solo procesamos la hoja de datos (índice 0); evita parsear hojas extra vacías del archivo
    public function sheets(): array
    {
        return [0 => $this];
    }

    public function collection(Collection $rows)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        DB::transaction(function () use ($rows) {
            $allProducts = Product::all()->keyBy('code_product');
            $allUnits = UnidadSat::all()->keyBy('clave_unidad');

            // índice de "código relacionado" (columna F) -> filas, construido una sola vez.
            // Antes esto se recalculaba re-escaneando todas las filas por cada producto (O(n²)),
            // lo que en catálogos grandes tardaba minutos en equipos más lentos.
            $positionsByRelatedCode = [];
            foreach ($rows as $index => $row) {
                if ($index <= 1) continue;
                $relatedCode = trim((string) ($row[5] ?? ''));
                if ($relatedCode === '') continue;
                if (!isset($positionsByRelatedCode[$relatedCode])) {
                    $positionsByRelatedCode[$relatedCode] = [];
                }
                if (count($positionsByRelatedCode[$relatedCode]) < 100) {
                    $positionsByRelatedCode[$relatedCode][] = $index;
                }
            }

            $partToProductToInsert = [];
            foreach ($rows as $index => $row) {
                if ($index <= 1) continue;
                $code_product = trim((string) $row[0]);
                if ($code_product === '') continue;

                $stock = $row[4] ?? 0;
                $stockStr = (string) $stock;
                if (strpos($stockStr, '.') !== false && substr_count($stockStr, '.') > 1) {
                    // si hay más de un punto, el primero es separador de miles
                    $stockStr = str_replace('.', '', $stockStr);
                }
                $stockStr = str_replace(',', '.', $stockStr);
                $stockValue = (float) $stockStr;

                $product = $allProducts->get($code_product);
                if (!$product) {
                    $this->skipped++;
                    $this->skippedCodes[] = $code_product;
                    continue;
                }
                $this->matched++;

                $positions = $positionsByRelatedCode[$code_product] ?? [];
                if (count($positions)) {
                    foreach ($positions as $item) {
                        $code_bar = (string) $rows[$item][6];
                        $code_bar = strtok($code_bar, '.');
                        $equivalencia = $rows[$item][7];

                        if (!$code_bar) continue;

                        $unit_sat = $allUnits->get($product->unit);
                        if (!$unit_sat) continue;

                        // equivalencia == 0 no es un factor de conversion valido (causaria division
                        // entre cero); se trata igual que "sin despiece", igual que equivalencia == 1.
                        $cantidad_despiece = 0;
                        if ($equivalencia > 0 && $equivalencia != 1) {
                            $cantidad_despiece = $equivalencia < 1
                                ? (1 / $equivalencia)
                                : (100 / $equivalencia);
                        }

                        $partToProductToInsert[] = [
                            'product_id'        => $product->id,
                            'code_bar'          => $code_bar,
                            'unidad_sat_id'     => $unit_sat->id,
                            'price_mayoreo'     => $product->precio_mayoreo,
                            'price'             => $cantidad_despiece > 0
                                                    ? ($product->precio_despiece / $cantidad_despiece)
                                                    : $product->precio,
                            'cantidad_despiezado' => $cantidad_despiece > 0 ? $cantidad_despiece : 0,
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ];
                    }
                } else {
                    $unit_sat = $allUnits->get($product->unit);
                    if (!$unit_sat) continue;

                    $partToProductToInsert[] = [
                        'product_id'        => $product->id,
                        'code_bar'          => 'N/A',
                        'unidad_sat_id'     => $unit_sat->id,
                        'price_mayoreo'     => $product->precio_mayoreo,
                        'price'             => $product->precio,
                        'cantidad_despiezado' => 0,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }

                // Actualizar existencia del producto
                $product->existence = $stockValue >= 0.1 ? $stockValue : 0;
                $product->save();
            }

            // Guardar todos los PartToProduct en lote (con upsert)
            foreach (array_chunk($partToProductToInsert, 500) as $batch) {
                PartToProduct::upsert(
                    $batch,
                    ['code_bar', 'product_id'], // clave única
                    ['unidad_sat_id','price_mayoreo','price','cantidad_despiezado','updated_at'] // columnas a actualizar
                );
            }
        });

        if ($this->skipped > 0) {
            Log::warning("Import de productos: {$this->skipped} códigos sin coincidencia en catálogo local.", [
                'codes' => array_slice($this->skippedCodes, 0, 200),
                'total_skipped' => $this->skipped,
            ]);
        }
    }

    //funcion para obtener las posiciones de las equivalencias y codigos de barras
    private function getPositions($rows, $code_product){
        $contador = 0;
        foreach($rows as $index => $item){
            $code_product_match = trim((string) $item[5]);
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
