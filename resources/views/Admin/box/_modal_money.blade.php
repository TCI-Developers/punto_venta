<!-- Modal -->
<div class="modal" id="modal_money" tabindex="-1" aria-labelledby="modal_moneyLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false" wire:ignore>
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modal_moneyLabel">Efectivo registrado</h5>
        </div>
        <div class="modal-body">
                <div class="row">
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
                            <tr id="tickets" class="text-center">
                              <td field="ticket_1000" valor='1000'></td>
                              <td field="ticket_500" valor='500'></td>
                              <td field="ticket_200" valor='200'></td>
                              <td field="ticket_100" valor='100'></td>
                              <td field="ticket_50" valor='50'></td>
                              <td field="ticket_20" valor='20'></td>
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
                            <tr id="coins" class="text-center">
                              <td field="coin_20" valor="20"></td>
                              <td field="coin_10" valor="10"></td>
                              <td field="coin_5" valor="5"></td>
                              <td field="coin_2" valor="2"></td>
                              <td field="coin_1" valor="1"></td>
                              <td field="coin_50_cen" valor=".50"></td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                </div> 
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onClick="btnCloseModal()">Cerrar</button>
        </div>
    </div>
  </div>
</div>