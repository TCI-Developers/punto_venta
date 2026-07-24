<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gastos de caja</title>
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
                <h2>Gastos de caja</h2>
            </div>

            @if(!is_object($box))
            <div class="card-body">
                <div class="alert alert-warning text-center mb-0">No tienes un turno abierto. Abre un turno para poder registrar gastos.</div>
            </div>
            @else
            <div class="card-body">
                <form action="{{route('gasto.store')}}" method="post" class="row g-2 align-items-end mb-4">
                    @csrf
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <label for="concepto" class="form-label">Concepto</label>
                        <input type="text" class="form-control" name="concepto" id="concepto" placeholder="Ej. Garrafón de agua" value="{{old('concepto')}}" required>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-12">
                        <label for="monto" class="form-label">Monto</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="monto" id="monto" placeholder="0.00" value="{{old('monto')}}" required>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-3 col-sm-12">
                        <label for="description" class="form-label">Descripción (opcional)</label>
                        <input type="text" class="form-control" name="description" id="description" placeholder="Notas adicionales" value="{{old('description')}}">
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12">
                        <button type="submit" class="btn btn-success w-100"><i class="fa fa-plus"></i>&nbsp;Registrar</button>
                    </div>
                </form>

                <table class="table table-striped table-bordered datatable">
                    <thead>
                        <tr class="text-center">
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Descripción</th>
                            <th>Monto</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gastos as $item)
                        <tr>
                            <td class="text-center">{{$item->created_at->format('d/m/Y H:i')}}</td>
                            <td>{{$item->concepto}}</td>
                            <td>{{$item->description}}</td>
                            <td class="text-right">$ {{number_format($item->monto, 2)}}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-info btn-sm" onclick="loadTicketGasto({{$item->id}})"><i class="fa fa-print"></i></button>
                                <a href="{{route('gasto.destroy', $item->id)}}" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este gasto?');"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="table-warning text-center">Sin gastos registrados en este turno.</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($gastos))
                    <tfoot>
                        <tr class="text-center">
                            <th colspan="3" class="text-right">Total de gastos del turno:</th>
                            <th class="text-right">$ {{number_format($total_gastos, 2)}}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @endif
        </div>
        @if(is_object($box))
        @include('Admin.gastos._modal_ticket')
        @endif
    </main>

    @if(session('gasto_ticket'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            loadTicketGasto({{ session('gasto_ticket') }});
        });
    </script>
    @endif
</body>
</html>
