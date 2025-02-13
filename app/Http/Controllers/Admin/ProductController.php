<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Product, PresentationProduct, UnidadSat, Box, Promotion, PartToProduct};
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;

class ProductController extends Controller
{
    public function __construct(){
        $this->middleware(function ($request, $next) {
        if($this->sucursalUser() === false){
            return redirect()->route('branchs.index')->with('error', 'Selecciona una sucursal para poder acceder al sistema.');
        }
        return $next($request);
        });
    }

    //listado de productos
    public function index(){   
        return view('admin.products.index', ['branch_id' => Auth::User()->branch_id]);
    }

    //funcion para mostrar presentacion/devolucion/promociones
    public function create($product_id){
        $presentations = PresentationProduct::where('status', 1)->get();
        $promotions = Promotion::where('status', 1)->get();
        $part_to_products = PartToProduct::where('product_id', (int)$product_id)->where('status', 1)->get();
        $unidades_sat = UnidadSat::where('status', 1)->get();

        $arr_presentations = [];
        if(count($part_to_products)){
            foreach($part_to_products as $index => $item){
                $arr_presentations[$index] = $item->getPresentation->type;
            }
        }

        return view('Admin.products.asignar_presentacion_desc_promo', ['product_id' => $product_id,'presentations' => $presentations, 
                    'promotions' => $promotions, 'part_to_products' => $part_to_products, 'presentation_name' => $arr_presentations,
                    'unidades_sat' => $unidades_sat]);
    }

    //funcion para guardar la presentacion/devolucion/promociones asignadas al producto
    public function store(Request $request, $product_id){
        if($request->presentation_type_id != '' && $request->price != '' && $request->code_bar != ''){
            if($request->part_product_id == ''){
                $presentation = new PartToProduct();
                $presentation->product_id = (int)$product_id;
                $message = 'asginada'; 
            }else{
                $presentation = PartToProduct::find($request->part_product_id);
                $message = 'actualizada';

            }

            if($request->monto_porcentaje > 0 && $request->monto_porcentaje != ''){
                $presentation->tipo_descuento = $request->tipo_descuento;
                $presentation->monto_porcentaje = $request->monto_porcentaje;
                $presentation->vigencia_cantidad_fecha = $request->vigencia_cantidad_fecha;
                $presentation->vigencia = $request->vigencia_cantidad_fecha == 'fecha' ? $request->vigencia_fecha:$request->vigencia;
            }

            $presentation->presentation_product_id = (int)$request->presentation_type_id;
            $presentation->price = $request->price;
            $presentation->code_bar = $request->code_bar;
            $presentation->stock = $request->stock;
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
