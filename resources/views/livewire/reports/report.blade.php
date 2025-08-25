<div class="card card-primary">
    <div class="form-group card-header with-border text-center">
        <h2>Reportes</h2>
    </div>
    <div class="card card-body">
        <div class="table-responsive">
            <div class="row col-12" style="justify-content: center; align-items: center;">
                <div class="col-lg-3 col-md-12 col-sm-12">
                    <input type="date" class="form-control" id="startDate" name="startDate" wire:model="startDate">
                </div>
                    
                <div class="col-lg-3 col-md-12 col-sm-12">
                    <input type="date" class="form-control" id="endDate" name="endDate" wire:model="endDate">
                </div>
                    
                <div class="col-lg-2 col-md-12 col-sm-12">
                    <button type="button" class="btn btn-success btn-sm mt-2" style="width:100%;" wire:click="filtrar(true)">Filtrar</button>
                </div>
                <div class="col-lg-2 col-md-12 col-sm-12">
                    <button type="button" class="btn btn-secondary btn-sm mt-2" style="width:100%" wire:click="filtrar(false)">Quitar Filtros</button>
                </div>
            </div>
            <hr>
            <div class="col-12 mt-2" style="display:flex; justify-content: space-between;">
                <label for="" class="col-lg-2 col-md-12 col-sm-12">
                    <a href="{{route('report.pdf', [$startDate, $endDate, $search])}}" class=" btn btn-danger btn-sm" target="_blank"><i class="fa fa-file"></i> PDF</a>
                </label>
                <label for="col-lg-6 col-md-12 col-sm-12">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <input type="text" class="form-control" placeholder="Buscar por código" aria-describedby="btnGroupAddon" wire:model="search">
                            <button type="button" class="btn btn-primary btn-sm input-group-text" id="btnGroupAddon" wire:click="searchProduct"><i class="fa fa-search"></i></button>
                            </div>
                    </div>
                </label>
            </div>

            <table class="table table-striped table-bordered mt-2">
                <thead>
                    <tr class="text-center text-sm">
                        <th>Codigo del Producto</th>
                        <th>Descripcion </th>
                        <th>Linea</th>
                        <th>Existencia Inicial</th>
                        <th>Total Entrada</th>
                        <th>Total Salida</th>
                        <th>Existencia real</th>
                        <th>Costo U</th>
                        <th>Costo Inventario </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $item)
                        <tr class="text-xs">
                            <td>{{ $item['codigo'] ?? '' }}</td>
                            <td>{{ $item['descripcion'] ?? '' }}</td>
                            <td>{{ $item['linea'] ?? '' }}</td>
                            <td class="text-right">{{ number_format($item['existencia_inicial'] ?? 0, 2) }}</td>
                            <td class="text-right">{{ number_format($item['total_entrada'] ?? 0, 2) }}</td>
                            <td class="text-right">{{ number_format($item['total_salida'] ?? 0, 2) }}</td>
                            <td class="text-right">{{ number_format($item['existencia_real'] ?? 0, 2) }}</td>
                            <td class="text-right">$ {{ number_format($item['costo_u'] ?? 0, 2) }}</td>
                            <td class="text-right">$ {{ number_format($item['costo_inventario'] ?? 0, 2) }}</td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="table-warning text-center">Sin Información</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="pagination d-flex gap-1" style="justify-content: flex-end;">
                {{-- Botón anterior --}}
                <button wire:click="prevPage" class="btn btn-secondary" style="display: {{ ($page <= 1) ? 'none':'block'}}">
                    &lt;
                </button>

                @php
                    $window = 10; // cantidad de botones a mostrar
                    $start = max(1, $page - intval($window / 2));
                    $end = min($totalPages, $start + $window - 1);

                    // Ajustar start si estamos cerca del final
                    if ($end - $start + 1 < $window) {
                        $start = max(1, $end - $window + 1);
                    }
                @endphp

                {{-- Botones de página --}}
                @for($i = $start; $i <= $end; $i++)
                    <button wire:click="goToPage({{ $i }})"
                            class="btn {{ $i === $page ? 'btn-primary' : 'btn-secondary' }}">
                        {{ $i }}
                    </button>
                @endfor

                {{-- Botón siguiente --}}
                <button wire:click="nextPage" class="btn btn-secondary" @if($page >= $totalPages) disabled @endif>
                    &gt;
                </button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const start = document.getElementById('startDate');
        const end = document.getElementById('endDate');

        // Evitar que la fecha de fin sea menor a la de inicio
        start.addEventListener('change', function() {
            end.min = start.value;
        });

        end.addEventListener('change', function() {
            start.max = end.value;
        });
    });
    </script>
</div>