<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devoluciones</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js']) 
    @include('components.use.link_scripts_glabal')
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error') 

    <div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Crear Devolución Matriz {{$branch->name ?? ''}}</h2>
        </div>
        <form action="{{route('devoluciones.storeMatriz')}}" method="post">
        @csrf
        <input type="hidden" name="branch_id" value="{{$branch->id}}">
        <div class="card-body">
            <div class="form-group">
                <a class="btn btn-success" href="{{route('devoluciones.index')}}"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Atras"><i class="fa fa-arrow-left"></i></a>                
            </div>
            <div class="col-12"><hr></div>
            <div class="row form-group">
                <label for="product_id" class="col-lg-4 col-sm-12">Producto a devolver*
                    <select name="product_id" id="product_id" class="form-control selectpicker" data-live-search="true" title="Selecciona producto a devolver" required>
                        <option value=""></option>
                        @foreach($products ?? [] as $item)
                            <option value="{{$item->id}}">{{$item->code_product}} - {{$item->description}}</option>
                        @endforeach
                    </select>
                </label>
                <label for="driver_id" class="col-lg-4 col-sm-12">Chofer
                    <select name="driver_id" id="driver_id" class="form-control selectpicker" data-live-search="true" title="Selecciona el chofer">
                        <option value=""></option>
                        @foreach($drivers as $item)
                            <option value="{{$item->id}}">{{$item->name}}</option>
                        @endforeach
                    </select>
                </label>
                <label for="cant" class="col-lg-4 col-sm-12">Cantidad a devolver*
                    <input type="number" class="form-control" name="cant" placeholder="0" step="0.01" required>
                </label>

                <label for="description" class="col-12">Nota
                   <textarea class="form-control" name="description" id="description" rows="10"></textarea>
                </label>
            </div>
            
        </div>
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Crear</button>
        </div>
        </form>
  </div>

</main>   
</body>
</html>