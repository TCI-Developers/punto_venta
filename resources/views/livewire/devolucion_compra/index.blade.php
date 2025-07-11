
<div class="card card-primary" style="height:90vh;">
    <div class="card-header">
        <h2 class="text-center">
            <a href="{{route('devoluciones.index')}}" class="btn btn-success btn-sm float-left"><i class="fa fa-arrow-left"></i></a>
            Listado de compras
        </h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            
            <div class="col-lg-2 col-md-2 col-sm-12 float-right">
            <div class="row">
                <label for="search" class="col-lg-12 col-md-12 col-sm-12">Buscar <br>
                    <input type="text" class="form-control" id="search" value="{{$search}}" placeholder="Buscar" wire:model.live="search" step="10">
                </label>
            </div>
            </div>

            <table class="table table-striped table-bordered datatable" id="table">
                <thead>
                    <tr class="text-center table-info">
                        <th colspan="5">Compras</th>
                    </tr>
                    <tr class="text-center">
                        <th>Folio</th>
                        <th>Usuario</th>
                        <th>Fecha</th>
                        <th>Total Venta</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($compras ?? [] as $item)
                        @if(!is_object($item->hasDevolution))
                        <tr class="text-center">
                            <td>{{$item->folio}}</td>
                            <td>{{$item->user}}</td>
                            <td>{{date('d-m-Y', $item->date)}}</td>
                            <td>$ {{number_format($item->total, 2)}}</td>
                            <td>
                                <a href="{{route('devoluciones.showMatriz', $item->id)}}" class="btn btn-warning btn-sm"><i class="fa fa-refresh"></i></a>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="5" class="table-warnign">Sin registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
