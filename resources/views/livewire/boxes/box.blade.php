<div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Cortes de caja</h2>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered datatable">
                <thead>
                    <tr class="text-center">
                        <th>Fecha</th>
                        <th>Caja Inicial</th>
                        <th>Total Tarjeta</th>
                        <th>Total Efectivo</th>
                        <th>Horario</th>
                        <th>status</th>
                        <th>Total</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($boxes as $index => $item)
                        <tr class="text-center clickable" style="cursor:pointer;" data-toggle="tooltip" data-placement="top" title="Mostrar Detalles" onClick="clickTr({{$index}})">
                        {{--onClick="clickTr({{$index}})"--}}
                            <td>{{date('d/m/y', strtotime($item->start_date))}}</td>
                            <td>$ {{number_format($item->start_amount_box, 2)}}</td>
                            <td>$ {{number_format($item->amount_credit_system, 2)}}</td>
                            <td>$ {{number_format($item->amount_cash_system, 2)}}</td>
                            <td>{{date('d/m/y H:i', strtotime($item->start_date))}} - {{date('d/m/y H:i', strtotime($item->end_date))}}</td>
                            <td>
                                @if($item->status == 0)
                                    <span class="badge badge-secondary">Sin cerrar</span>
                                @elseif($item->status == 1)
                                    <span class="badge badge-success">Completada</span>
                                @else
                                    <span class="badge badge-warning">Completada Irregular</span>
                                    @if((int)$item->amount_credit_user != (int)$item->amount_credit_system)
                                    <br><span class="badge {{(int)$item->amount_credit_system > (int)$item->amount_credit_user ? 'badge-danger':'badge-primary'}}">
                                        {{($item->amount_credit_system - $item->amount_credit_user) < 0 ? '+':'-'}} $ {{number_format(abs($item->amount_credit_system - $item->amount_credit_user),2)}}
                                    </span>
                                    @endif
                                    @if((int)$item->amount_cash_user != (int)$item->amount_cash_system)
                                    <br><span class="badge {{(int)$item->amount_cash_system > (int)$item->amount_cash_user ? 'badge-danger':'badge-primary'}}">
                                        {{($item->amount_cash_system - $item->amount_cash_user) < 0 ? '+':'-'}} $ {{number_format(abs($item->amount_cash_system - $item->amount_cash_user),2)}}
                                    </span>
                                    @endif
                                @endif
                            </td>
                            <td>$ {{number_format(($item->amount_cash_system + $item->start_amount_box + $item->amount_credit_system),2)}}</td>
                            <td><button type="button" class="btn btn-outline-info btn-sm" wire:click="openModalMoney({{$item->id}})"
                            ><img src="{{asset('icons/money.svg')}}" alt="icon money"></button></td>
                        </tr>
                        <tr class="text-center collapse" id="group-of-rows-{{$index}}">
                            <td colspan="2">Usuario Registró:</td>
                            <td>$ {{number_format($item->amount_credit_user,2)}}</td>
                            <td>$ {{number_format($item->amount_cash_user,2)}}</td>
                            <td colspan="2">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-6">
                                        <span>Efectivo diferencia de:</span> <br>
                                        @if(($item->amount_cash_system - ($item->amount_cash_user - $item->start_amount_box)) == 0)
                                        $ {{number_format(abs($item->amount_cash_system - ($item->amount_cash_user - $item->start_amount_box)),2) }}
                                        @else
                                        {{($item->amount_cash_system - ($item->amount_cash_user - $item->start_amount_box)) < 0 ? '+':'-'}} $ {{number_format(abs($item->amount_cash_system - ($item->amount_cash_user - $item->start_amount_box)),2)}}
                                        @endif
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6">
                                        <span>Tarjetas diferencia de:</span> <br>
                                        @if(($item->amount_credit_system - $item->amount_credit_user) == 0)
                                        $ {{number_format(abs($item->amount_credit_system - $item->amount_credit_user),2)}}
                                        @else
                                        {{($item->amount_credit_system - ($item->amount_credit_user)) < 0 ? '+':'-'}} $ {{number_format(abs($item->amount_credit_system - $item->amount_credit_user),2)}}
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>$ {{number_format(($item->amount_cash_user + $item->amount_credit_user),2)}}</td>
                        </tr>
                @empty
                    <tr class="table-warning text-center" colspan="7"></tr>
                @endforelse
            </table>
        </div>
        @include('Admin.box._modal_money')
    </div>