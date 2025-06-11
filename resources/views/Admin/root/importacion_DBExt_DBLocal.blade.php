 <div class="card-body">
    <div class="row">
        <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
            <a href="{{ route('import.dataLocal', ['User', 'users']) }}" class="btn btn-info"><i class="fa fa-download"></i> Usuarios</a>
        </label>
        <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
            <a href="{{ route('import.dataLocal', ['Brand', 'brands']) }}" class="btn btn-info"><i class="fa fa-download"></i> Marcas</a>
        </label>
        <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
            <a href="{{ route('import.dataLocal', ['Product', 'products']) }}" class="btn btn-info"><i class="fa fa-download"></i> Productos</a>
        </label>
        <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
            <a href="{{ route('import.dataLocal', ['EmpresaDetail', 'empresa_details'])}}" class="btn btn-info"><i class="fa fa-download"></i> Datos empresa</a>
        </label>
        <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
            <a href="{{ route('import.dataLocal', ['Driver', 'drivers']) }}" class="btn btn-info"><i class="fa fa-download"></i> Choferes</a>
        </label>
        <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
            <a href="{{ route('import.dataLocal', ['PaymentMethod', 'payment_methods']) }}" class="btn btn-info"><i class="fa fa-download"></i> Metodos de pago</a>
        </label>
        <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
            <a href="{{ route('import.dataLocal', ['UnidadSat', 'unidades_sat']) }}" class="btn btn-info"><i class="fa fa-download"></i> Unidades SAT</a>
        </label>
        <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
            <a href="{{ route('import.dataLocal', ['Proveedor', 'proveedores']) }}" class="btn btn-info"><i class="fa fa-download"></i> Proveedores</a>
        </label>
        <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
            <a href="{{ route('import.dataLocal', ['Branch', 'branchs']) }}" class="btn btn-info"><i class="fa fa-download"></i> Sucursales</a>
        </label>
        @if(Auth::User()->name == 'TCI_DEV')
        <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
            <button type="button" class="btn btn-primary" onclick="showModal()"><i class="fa fa-download"></i> Configuración Inicial</button>
        </label>
        @endif
        <label class="col-lg-4 col-ms-6 col-sm-12 text-center">
            <form action="{{ route('resetDatabase') }}" method="POST" onsubmit="return confirm('¿Estás seguro de restaurar TCI POS?')">
                @csrf
                <button type="submit" class="btn btn-danger"><i class="fa fa-download"></i> Restaurar TCI POS</button>
            </form>
        </label>
    </div>
</div>
 @include('Admin.root._modal')

    <script>
        function showModal(type = 'show'){
            if(type == 'show'){
                $('#modal_conf').show();
            }else{
                $('#modal_conf').hide();
                $('input[name=password]').val('');
            }
        }
    </script>