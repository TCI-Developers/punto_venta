<!-- Modal -->
<div class="modal" id="modal_box" tabindex="-1" aria-labelledby="modal_createLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
          <h4 class="col-lg-12" id="modal_createLabel"><span id="title">Cierre</span> de turno
            <p class="float-right">{{!count($ventas_cerradas) ? 'No se registraron ventas.':'Numero de ventas: '.count($ventas_cerradas)}}</p>
          </h4> 
      </div>
      @if($status != 2)
      <form action="{{route('box.store')}}" method="post">
      @csrf
        <input type="hidden" name="id">
          <div class="modal-body">
                <div class="row">
                      <p class="col-lg-12 col-md-12 col-sm-12 text-xs text-center">Por favor, siga los siguientes pasos para proceder con el cierre del turno:</p>
                      <p class="col-lg-12 col-md-12 col-sm-12 text-xs text-center">Total de Dinero en Caja: Ingrese el monto total de dinero en efectivo que hay en la caja.</p>
                      <p class="col-lg-12 col-md-12 col-sm-12 text-xs text-center">Cantidad de Billetes y Monedas: <br> Para cada denominación de billetes y monedas, ingrese la cantidad de cada uno que hay en la caja. <br>
                              Por ejemplo:</p> 
                             <p class="col-lg-3 col-md-3 col-sm-12 text-sm  text-center"> Billetes de $100: 5 </p>
                             <p class="col-lg-3 col-md-3 col-sm-12 text-sm  text-center"> Billetes de $50: 10 </p>  
                             <p class="col-lg-3 col-md-3 col-sm-12 text-sm  text-center"> Monedas de $10: 20 </p>  
                             <p class="col-lg-3 col-md-3 col-sm-12 text-sm  text-center"> Monedas de $1: 50 </p>
                      <br>

                      <label for="monto_efectivo" class="col-lg-6 col-md-6 col-sm-12 text-center">¿Cuanto efectivo hay en caja? <br>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control text-center inputModal" name="monto_efectivo" id="monto_efectivo" placeholder="0" value="{{old('monto_efectivo')}}" step="0.01">
                        </div>
                      </label>

                      <label for="monto_tarjeta" class="col-lg-6 col-md-6 col-sm-12 text-center">¿Cuanto se vendio con tarjeta? <br>
                          <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                  <span class="input-group-text">$</span>
                              </div>
                              <input type="number" class="form-control text-center inputModal" name="monto_tarjeta" id="monto_tarjeta" placeholder="0" value="{{old('monto_tarjeta') ?? 0}}" step="0.01">
                          </div>
                      </label>                    

                      <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered">
                          <thead>
                            <tr><th colspan="4" class="table-info">Billetes</th><th class="text-center table-info" id="totalTickets" colspan="2">Total: $0</th></tr>
                            <tr>
                              <th>$1000</th>
                              <th>$500</th>
                              <th>$200</th>
                              <th>$100</th>
                              <th>$50</th>
                              <th>$20</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td><input type="number" class="form-control tickets" valor="1000" name="tickets[1000]" placeholder="0" onchange="totalTicketsCoins('tickets')" value="{{old('tickets.1000')}}"></td>
                              <td><input type="number" class="form-control tickets" valor="500" name="tickets[500]" placeholder="0" onchange="totalTicketsCoins('tickets')" value="{{old('tickets.500')}}"></td>
                              <td><input type="number" class="form-control tickets" valor="200" name="tickets[200]" placeholder="0" onchange="totalTicketsCoins('tickets')" value="{{old('tickets.200')}}"></td>
                              <td><input type="number" class="form-control tickets" valor="100" name="tickets[100]" placeholder="0" onchange="totalTicketsCoins('tickets')" value="{{old('tickets.100')}}"></td>
                              <td><input type="number" class="form-control tickets" valor="50" name="tickets[50]" placeholder="0" onchange="totalTicketsCoins('tickets')" value="{{old('tickets.50')}}"></td>
                              <td><input type="number" class="form-control tickets" valor="20" name="tickets[20]" placeholder="0" onchange="totalTicketsCoins('tickets')" value="{{old('tickets.20')}}"></td>
                            </tr>
                          </tbody>
                          <thead>
                            <tr><th colspan="4" class="table-info">Monedas</th><th class="text-center table-info" id="totalCoins" colspan="2">Total: $0</th></tr>
                            <tr>
                              <th>$20</th>
                              <th>$10</th>
                              <th>$5</th>
                              <th>$2</th>
                              <th>$1</th>
                              <th>$.50</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td><input type="number" class="form-control coins" valor="20" onchange="totalTicketsCoins('coins')" name="coins[20]" placeholder="0" value="{{old('coins.20')}}"></td>
                              <td><input type="number" class="form-control coins" valor="10" onchange="totalTicketsCoins('coins')" name="coins[10]" placeholder="0" value="{{old('coins.10')}}"></td>
                              <td><input type="number" class="form-control coins" valor="5" onchange="totalTicketsCoins('coins')" name="coins[5]" placeholder="0" value="{{old('coins.5')}}"></td>
                              <td><input type="number" class="form-control coins" valor="2" onchange="totalTicketsCoins('coins')" name="coins[2]" placeholder="0" value="{{old('coins.2')}}"></td>
                              <td><input type="number" class="form-control coins" valor="1" onchange="totalTicketsCoins('coins')" name="coins[1]" placeholder="0" value="{{old('coins.1')}}"></td>
                              <td><input type="number" class="form-control coins" valor=".50" onchange="totalTicketsCoins('coins')" name="coins[_50]" placeholder="0" value="{{ old('coins._50') }}"></td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                      <div class="row col-lg-12 col-md-12 col-sm-12" style="display: flex; flex-direction: row; flex-wrap: nowrap; align-content: center; align-items: center; justify-content: flex-start;">
                        <input type="checkbox" name="acept" class="form-control col-lg-1"> Cerrar turno con montos ingresados.
                      </div>
                </div>
          </div>
          <div class="modal-footer">
              <p class="text-md text-info">Tu caja inicial fue de: $ {{$start_amount_box ?? 0}}</p>
              <a href="{{route('sale.index')}}" class="btn btn-light text-dark">
                <i class="fa fa-times"></i>&nbsp;Cancelar
              </a>
              <button type="submit" class="btn btn-success text-white" id="btnAddEdit">
                <i class="fa fa-arrow-right"></i>&nbsp;Siguiente</button>
          </div>
      </form>
      @else
      <div class="modal-body">
            <div class="row col-12">
                <h3 class="text-center">Tienes ventas sin cerrar.</h3>
            </div>
        </div>
        <div class="modal-footer">
            <a href="{{route('sale.index')}}" class="btn btn-light text-dark"><i class="fa fa-times"></i>&nbsp;Regresar</a>
        </div>
      @endif
    </div>
  </div>
  @include('Admin.box._modal_ticket')
</div>