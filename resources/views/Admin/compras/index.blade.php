<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compras</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
    <script>
        function toggleDropdown(button) {
            const menu = button.nextElementSibling;
            menu.classList.toggle('show');
            
            // Cerrar si se hace clic fuera
            document.addEventListener('click', function handleClickOutside(event) {
                if (!button.contains(event.target) && !menu.contains(event.target)) {
                    menu.classList.remove('show');
                    document.removeEventListener('click', handleClickOutside);
                }
            });
        }
    </script>
</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')
        <div class="card card-primary" style="height:90vh;">
            <div class="form-group card-header with-border text-center">
                <h2>Compras</h2>            </div>

            <div class="card-body table-responsive">
                <div class="form-group">
                    <a href="{{route('compra.create')}}" class="btn btn-success"><i class="fa fa-plus"></i> &nbsp; Nueva</a>
                </div>

                <table class="table table-striped table-bordered datatable">
                    <thead>
                        <tr class="text-center">
                            <th>Folio</th>
                            <th>Proveedor</th>
                            <th>Fecha Requisición</th>
                            <th>Observaciones</th>
                            <th>Importe General</th>
                            <th>Tipo/Status</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($compras as $item)
                        <tr>
                            <td class="text-center">{{$item->folio}}</td>
                            <td>{{$item->getProveedor->name}}</td>
                            <td class="text-center">{{date('d-m-Y', strtotime($item->created_at))}}</td>
                            <td>{{$item->observaciones}}</td>
                            <td class="text-right">$ {{number_format($item->total, 2)}}</td>
                            <td class="text-center">
                                <span class="badge {{$item->tipo == 'OC' ? 'badge-success':'badge-info'}}">{{$item->tipo == 'OC' ? 'Orden de compra':'Servicio'}}</span> 
                                @if($item->getCuentaPagar)
                                <br>
                                @if($item->getCuentaPagar->status == 2)
                                    <span class="badge badge-success">Pagada</span>
                                @else
                                    <span class="badge {{$item->getCuentaPagar->status == 1 ? 'badge-warning':'badge-danger'}}">{{$item->getCuentaPagar->status == 1 ? 'Pendiente':'Cancelada'}}</span>
                                @endif
                                @endif
                            </td>
                            <td class="text-center">
                                 <div class="dropdown" style="position: relative; display: inline-block;">
                                     <button class="btn btn-primary btn-sm dropdown-toggle" type="button" onclick="toggleDropdown(this)">
                                        
                                        Acción
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="position:relative !important;">
                                        <a class="dropdown-item" href="{{route('compra.show', $item->id)}}"><i class="fa fa-eye"></i> &nbsp; Visualizar</a>
                                        <a class="dropdown-item" href="{{route('compra.pdf', $item->id)}}"><i class="fa fa-file"></i> &nbsp; PDF</a>
                                        @if($item->getCuentaPagar)
                                            <a class="dropdown-item" href="{{route('cxp.show', $item->getCuentaPagar->id)}}"><i class="fa fa-address-book"></i> &nbsp; Cuenta por pagar</a>
                                        @endif 
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="table-warning text-center">Sin compras</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </main>   
</body>
</html>