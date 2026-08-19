<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Product, PresentationProduct, UnidadSat, Box, Promotion, PartToProduct};
use Illuminate\Support\Facades\{DB,Auth,Log,Storage};
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
            if($request->part_product_id == '' || is_null($request->part_product_id)){
                    $exist_presentation = PartToProduct::where('product_id', $product_id)->first();
                    if(is_object($exist_presentation) && $exist_presentation->code_bar == $request->code_bar){
                        return redirect()->back()->with('info', 'Ya existe presentación de este producto.');
                    } 

                $presentation = new PartToProduct();
                $presentation->product_id = (int)$product_id;
                $message = 'asginada'; 
            }else{
                if(!auth()->user()->hasPermissionThroughModule('inventarios', 'punto_venta', 'update')){
                    return redirect()->back()->with('error', 'Acción no autorizada.');
                }
                $presentation = PartToProduct::find($request->part_product_id);
                $message = 'actualizada';
            }

            //descuentos
            if($request->monto_porcentaje != '' && $request->monto_porcentaje > 0){
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
            // $presentation->stock = $request->stock;
            $presentation->cantidad_mayoreo = $request->cantidad_mayoreo ?? 0;
            $presentation->cantidad_despiezado = $request->cantidad_despiezado ?? 0;

            $product_stock = Product::find($product_id);
            if(is_object($product_stock)){
                $product_stock->existence = $request->stock;
                $product_stock->save();
            }

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
        // se ponia dentro de ProductsImport::collection(), pero eso corre DESPUES de que el
        // paquete de Excel ya cargó/parseó todo el archivo en memoria -- para archivos grandes,
        // ese parseo inicial es el momento mas pesado, asi que el limite tiene que aplicar desde
        // aqui para que realmente sirva de algo.
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $request->validate([
            'excel_file' => 'required|mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

        try {
            // Algunos exports del cliente traen extensión .xls pero contenido real .xlsx (u otro
            // formato); detectamos el formato real del archivo en vez de confiar en la extensión.
            $readerType = IOFactory::identify($request->file('excel_file')->getRealPath());

            // si el archivo trae una novena columna "Precio" (precio real de despiece), se le
            // pregunta al usuario si quiere tomarla en vez de asumirlo -- se guarda el archivo
            // temporalmente y se le muestra la confirmación en la misma pantalla.
            if (ProductsImport::hasPriceColumn($request->file('excel_file')->getRealPath(), $readerType)) {
                $tempPath = $request->file('excel_file')->store('imports_pendientes');
                // ruta explicita (no back()) -- back() depende del header Referer, que en la app
                // de escritorio puede no llegar y mandar la respuesta a otra pantalla, dejando el
                // mensaje de exito/error sin mostrarse (aunque la importacion si haya corrido).
                return redirect()->route('product.showUploadExcel')->with('confirmExcelPrice', [
                    'path' => $tempPath,
                    'reader' => $readerType,
                    'nombre_original' => $request->file('excel_file')->getClientOriginalName(),
                ]);
            }

            $import = new ProductsImport();
            Excel::import($import, $request->file('excel_file'), null, $readerType);

            return redirect()->route('product.showUploadExcel')->with('success', $this->buildImportMessage($import));
        } catch (\Throwable $th) {
            // antes se mostraba "Excel Dañado." sin importar la causa real, haciendo
            // imposible diagnosticar el problema a distancia -- ahora se deja el error real
            // en el log para poder ver exactamente que tronó (memoria, formato, etc.).
            Log::error('Error al subir/procesar Excel de productos: '.$th->getMessage(), [
                'exception' => get_class($th),
                'archivo' => $request->file('excel_file')?->getClientOriginalName(),
            ]);
            return redirect()->route('product.showUploadExcel')->with('error', 'No se pudo procesar el archivo: '.$th->getMessage());
        }
    }

    //funcion para completar la importacion una vez que el usuario ya contesto si quiere usar
    //los precios de la columna "Precio" detectada en uploadExcel()
    public function confirmUploadExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $request->validate([
            'temp_path' => 'required|string',
            'reader' => 'required|string',
            'use_excel_price' => 'required|in:0,1',
        ]);

        if (!Storage::exists($request->temp_path)) {
            return redirect()->route('product.showUploadExcel')->with('error', 'El archivo ya no está disponible, vuelve a subirlo.');
        }

        try {
            $fullPath = Storage::path($request->temp_path);
            $import = new ProductsImport($request->boolean('use_excel_price'));
            Excel::import($import, $fullPath, null, $request->reader);

            return redirect()->route('product.showUploadExcel')->with('success', $this->buildImportMessage($import));
        } catch (\Throwable $th) {
            Log::error('Error al confirmar importación de Excel de productos: '.$th->getMessage(), [
                'exception' => get_class($th),
            ]);
            return redirect()->route('product.showUploadExcel')->with('error', 'No se pudo procesar el archivo: '.$th->getMessage());
        } finally {
            Storage::delete($request->temp_path);
        }
    }

    //mensaje comun de resultado para uploadExcel/confirmUploadExcel
    private function buildImportMessage(ProductsImport $import): string
    {
        $message = "Archivo procesado: {$import->matched} productos actualizados";
        if ($import->skipped > 0) {
            $message .= ", {$import->skipped} sin coincidencia en el catálogo (revisar log).";
        } else {
            $message .= '.';
        }
        return $message;
    }

    //funcion para vista de inventarios
    public function indexInventarios(){
        return view('Admin.inventarios.index');
    }
}
