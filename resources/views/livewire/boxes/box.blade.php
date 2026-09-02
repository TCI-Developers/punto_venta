<div>
    {{-- ============================================================
         PANEL DE DIAGNÓSTICO — solo visible para root / TCI_DEV
         Muestra todos los turnos con status=0 (abiertos / atascados)
         ============================================================ --}}
    @if(auth()->user()->hasRole('root') || auth()->user()->name === 'TCI_DEV')
        @if(count($turnosAbiertos))
        <div class="card card-danger mb-3">
            <div class="card-header with-border">
                <h4 class="mb-0">
                    <i class="fa fa-exclamation-triangle text-danger"></i>
                    Turnos abiertos / atascados
                    <span class="badge badge-danger ml-2">{{ count($turnosAbiertos) }}</span>
                </h4>
                <small class="text-muted">Solo visible para administradores root</small>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr class="text-center">
                            <th>#</th>
                            <th>Usuario</th>
                            <th>Apertura</th>
                            <th>Tiempo abierto</th>
                            <th>Monto inicial</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($turnosAbiertos as $t)
                        @php
                            $inicio  = \Carbon\Carbon::parse($t->start_date);
                            $diff    = $inicio->diffForHumans(null, false, false, 2);
                        @endphp
                        <tr class="text-center align-middle">
                            <td>{{ $t->id }}</td>
                            <td>{{ $t->user?->name ?? 'ID '.$t->user_id }}</td>
                            <td>{{ $inicio->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $inicio->diffInHours() > 12 ? 'badge-danger' : 'badge-warning' }}">
                                    {{ $diff }}
                                </span>
                            </td>
                            <td>$ {{ number_format($t->start_amount_box, 2) }}</td>
                            <td>
                                {{-- Forzar cierre --}}
                                <button type="button"
                                    class="btn btn-sm btn-danger mr-1"
                                    wire:click="forceClose({{ $t->id }})"
                                    wire:confirm="¿Cerrar forzosamente el turno #{{ $t->id }} de {{ $t->user->name ?? 'este cajero' }}? Se marcara como completado sin recalcular montos."
                                    title="Forzar cierre">
                                    <i class="fa fa-lock"></i> Forzar cierre
                                </button>
                                {{-- Reabrir --}}
                                <button type="button"
                                    class="btn btn-sm btn-warning"
                                    wire:click="reopen({{ $t->id }})"
                                    wire:confirm="¿Reabrir el turno #{{ $t->id }}? El cajero podrá intentar cerrarlo nuevamente."
                                    title="Reabrir turno">
                                    <i class="fa fa-unlock"></i> Reabrir
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="alert alert-success mb-3 py-2">
            <i class="fa fa-check-circle"></i>
            <strong>Sin turnos atascados.</strong> Todos los turnos están cerrados correctamente.
        </div>
        @endif
    @endif

    {{-- ============================================================
         TABLA PRINCIPAL — historial de cierres de caja
         ============================================================ --}}
    <div class="card card-primary" style="min-height:80vh;">
        <div class="form-group card-header with-border text-center">
            <h2>Cortes de caja sucursal {{$user->getBranch?->name ?? ''}}</h2>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered datatable">
                <thead>
                    <tr class="text-center">
                        <th>Fecha</th>
                        <th>Caja Inicial</th>
                        <th>Total Tarjeta</th>
                        <th>Total Efectivo</th>
                        <th>Devoluciones</th>
                        <th>Gastos</th>
                        <th>Horario</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($boxes as $index => $item)
                    @php
                        $devs          = $item->getTotalDevolutions($item->start_date, $item->end_date ?? now());
                        $expected_cash = ($item->start_amount_box + $item->amount_cash_system) - $devs - $item->total_gastos;
                        $cash_diff     = $expected_cash - $item->amount_cash_user;
                    @endphp
                    <tr class="text-center clickable" style="cursor:pointer;"
                        data-toggle="tooltip" data-placement="top" title="Mostrar Detalles"
                        onClick="clickTr({{$index}})">
                        <td>{{ date('d/m/y', strtotime($item->start_date)) }}</td>
                        <td>$ {{ number_format($item->start_amount_box, 2) }}</td>
                        <td>$ {{ number_format($item->amount_credit_system, 2) }}</td>
                        <td>$ {{ number_format($item->amount_cash_system, 2) }}</td>
                        <td>
                            <a href="{{ route('devoluciones.indexDevCorte', [$item->start_date, $item->end_date ?? now()]) }}"
                               class="badge badge-info">
                               $ {{ number_format($devs, 2) }}
                            </a>
                        </td>
                        <td>$ {{ number_format($item->total_gastos, 2) }}</td>
                        <td>
                            {{ date('d/m/y H:i', strtotime($item->start_date)) }}
                            @if($item->end_date)
                                - {{ date('d/m/y H:i', strtotime($item->end_date)) }}
                            @else
                                - <em class="text-warning">abierto</em>
                            @endif
                        </td>
                        <td>
                            @if($item->status == 0)
                                <span class="badge badge-secondary">Sin cerrar</span>
                            @elseif($item->status == 1)
                                <span class="badge badge-success">Completada</span>
                            @else
                                <span class="badge badge-warning">Completada Irregular</span>
                                @if((float)$item->amount_credit_user != (float)$item->amount_credit_system)
                                    <br><span class="badge {{ (float)$item->amount_credit_system > (float)$item->amount_credit_user ? 'badge-danger':'badge-primary' }}">
                                        {{ ($item->amount_credit_system - $item->amount_credit_user) < 0 ? '+':'-' }}
                                        $ {{ number_format(abs($item->amount_credit_system - $item->amount_credit_user), 2) }}
                                    </span>
                                @endif
                                @if(abs($cash_diff) > 1)
                                    <br><span class="badge {{ $cash_diff > 0 ? 'badge-danger':'badge-primary' }}">
                                        {{ $cash_diff < 0 ? '+':'-' }}
                                        $ {{ number_format(abs($cash_diff), 2) }}
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td>$ {{ number_format(($item->amount_credit_system + $expected_cash), 2) }}</td>
                        <td>
                            <button type="button" class="btn btn-outline-info btn-sm"
                                wire:click.stop="openModalMoney({{ $item->id }})"
                                title="Ver conteo de billetes">
                                <i class="fa fa-money"></i>
                            </button>
                        </td>
                    </tr>
                    <tr class="text-center collapse" id="group-of-rows-{{$index}}">
                        <td colspan="2">Usuario Registró:</td>
                        <td>$ {{ number_format($item->amount_credit_user, 2) }}</td>
                        <td>$ {{ number_format($item->amount_cash_user, 2) }}</td>
                        <td colspan="3">
                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-sm-6">
                                    <span>Efectivo diferencia de:</span><br>
                                    @if($cash_diff == 0)
                                        $ {{ number_format(0, 2) }}
                                    @else
                                        {{ $cash_diff < 0 ? '+':'-' }} $ {{ number_format(abs($cash_diff), 2) }}
                                    @endif
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6">
                                    <span>Tarjetas diferencia de:</span><br>
                                    @if(($item->amount_credit_system - $item->amount_credit_user) == 0)
                                        $ {{ number_format(0, 2) }}
                                    @else
                                        {{ ($item->amount_credit_system - $item->amount_credit_user) < 0 ? '+':'-' }}
                                        $ {{ number_format(abs($item->amount_credit_system - $item->amount_credit_user), 2) }}
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>$ {{ number_format(($item->amount_cash_user + $item->amount_credit_user), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="table-warning text-center" colspan="10">Sin registros.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            {{ $boxes->links() }}
        </div>
        @include('Admin.box._modal_money')
    </div>
</div>
