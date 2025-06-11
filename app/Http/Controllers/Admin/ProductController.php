<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Product, PresentationProduct, UnidadSat, Box, Promotion, PartToProduct};
use Illuminate\Support\Facades\{DB,Auth};
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;

class ProductController extends Controller
{
    //listado de productos
    public function index(){   
        return view('admin.products.index', ['branch_id' => Auth::User()->branch_id]);
    }

    //funcion para mostrar presentacion/devolucion/promociones
    public function create($product_id, $type = null){
        $product = Product::find($product_id);
        $part_to_products = PartToProduct::where('product_id', (int)$product_id)->where('status', 1)->get();

        if($type == 'despiece'){
            if(!count($part_to_products) || $product->precio_despiece <= 0.0){
                $message = !count($part_to_products) ? 'sin antes agregar una presentación':'porque no tiene precio despiezado.';
                return redirect()->back()->with('error', 'No puedes despiezar el producto '.$message.'.');
            }
        }

        $promotions = Promotion::where('status', 1)->get();
        $unidades_sat = UnidadSat::where('status', 1)->get();

        return view('Admin.products.asignar_presentacion_desc_promo', ['product' => $product, 'type' => $type,
                    'promotions' => $promotions, 'part_to_products' => $part_to_products,
                    'unidades_sat' => $unidades_sat]);
    }

    //funcion para guardar la presentacion/devolucion/promociones asignadas al producto
    public function store(Request $request, $product_id){
          

        if($request->unidad_sat_id != '' && $request->price != '' && $request->code_bar != ''){
            if($request->part_product_id == ''){
                    $exist_presentation = PartToProduct::where('product_id', $product_id)->first();
                    if(is_object($exist_presentation)){
                        return redirect()->back()->with('info', 'Ya existe presentación de este producto.');
                    } 

                $presentation = new PartToProduct();
                $presentation->product_id = (int)$product_id;
                $message = 'asginada'; 
            }else{
                $presentation = PartToProduct::find($request->part_product_id);
                $message = 'actualizada';
            }

            //descuentos
            if($request->monto_porcentaje > 0 && $request->monto_porcentaje != ''){
                $presentation->tipo_descuento = $request->tipo_descuento;
                $presentation->monto_porcentaje = $request->monto_porcentaje;
                $presentation->vigencia_cantidad_fecha = $request->vigencia_cantidad_fecha;
                $presentation->vigencia = $request->vigencia_cantidad_fecha == 'fecha' ? $request->vigencia_fecha:$request->vigencia;
            }
            //promocion
            if(isset($request->promotion_id)){
                $presentation->promotion_id = (int)$request->promotion_id ?? null;
            }

            $presentation->unidad_sat_id = (int)$request->unidad_sat_id;
            $presentation->price = $request->price;
            $presentation->price_mayoreo = $request->precio_mayoreo ?? 0;
            $presentation->code_bar = $request->code_bar;
            $presentation->stock = $request->stock;
            $presentation->cantidad_mayoreo = $request->cantidad_mayoreo ?? 0;
            $presentation->cantidad_despiezado = $request->cantidad_despiezado ?? 0;
            $presentation->save();

            return redirect()->back()->with('success', 'Presentación '.$message.' a producto.');
        }

        return redirect()->back()->with('error', 'Ocurrio un error inesperado.');
    }

    //fucnion para mostrar listado de despiezado
    public function indexPartProduct(){
        $presentations = PresentationProduct::where('status', 1)->get();
        $unidades_sat = UnidadSat::where('status', 1)->get();
        return view('admin.products.index_part_product', ['presentations' => $presentations, 'status' => 1, 'unidades_sat' => $unidades_sat]);
    }

    //fucnion para mostrar listado de despiezado deshabilitado
    public function indexPartProductDisabled(){
        $presentations = PresentationProduct::where('status', 0)->get();
        $unidades_sat = UnidadSat::where('status', 1)->get();
        return view('admin.products.index_part_product', ['presentations' => $presentations, 'status' => 0, 'unidades_sat' => $unidades_sat]);
    }

    //funcion guardar presentacion de productos
    public function storePresentationProduct(Request $request){
        $validatedData = $request->validate([
            'type' => 'required',
            'unidad_sat_id' => 'required',
        ]);

        $presentation = new PresentationProduct();
        $presentation->type = $request->type;
        $presentation->description = $request->description;
        $presentation->unidad_sat_id = $request->unidad_sat_id;
        $presentation->save();

        return redirect()->back()->with('success', 'Presentacion creada con exito.');
    }

    //funcion guardar presentacion de productos
    public function updatePresentationProduct(Request $request){
        $validatedData = $request->validate([
            'type' => 'required',
            'unidad_sat_id' => 'required',
        ]);

        $presentation = PresentationProduct::find($request->id);
        if(!is_object($presentation)){
            return redirect()->back()->with('error', 'Ocurrio un error.');
        }
        $presentation->type = $request->type;
        $presentation->description = $request->description;
        $presentation->unidad_sat_id = $request->unidad_sat_id;
        $presentation->save();

        return redirect()->back()->with('success', 'Presentacion actualizada con exito.');
    }

    //funcion para eliminar una presentación
    public function destroyPresentationProduct(string $id, $status){
        $presentation = PresentationProduct::find($id);
        if(!is_object($presentation)){
            return redirect()->back()->with('error', 'Ocurrio un error.');
        }
        $presentation->status = $status;
        $presentation->save();

        $message = $status == 0 ? 'inhabilitada':'habilitada';
        return redirect()->back()->with('success', 'Presentacion '.$message.' con exito.');
    }

    //funcion para abrir vista de carga de excel
    public function showUploadExcel(){
        return view('Admin.products.import_excel');
    }

    //funcion para cargar el excel y procesarlo
    public function uploadExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new ProductsImport, $request->file('excel_file'));

        return back()->with('success', 'Archivo procesado correctamente.');
    }
}
