<!-- Modal -->
<div class="modal modalTicket" id="modalTicket" tabindex="-1" aria-labelledby="modalTicketLabel"
  aria-hidden="true" data-backdrop="static" data-keyboard="false"
   wire:ignore>
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTicketLabel">Ticket</h5>
        <button type="button" class="btn btn-primary btn-sm" onclick="reimprimirDevolucion()"><i class="fa fa-print"></i> Imprimir</button>
        <a href="{{route('devoluciones.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-check"></i> Cerrar</a>
      </div>
      <div class="modal-body col-12">
          <iframe id="iframeDevolucion" src="http://127.0.0.1:8100/ticket-devolution/{{$devolution->id ?? 0}}/true" title="Tickets" style="width:100%; height:70vh;"></iframe>
      </div>
    </div>
  </div>
</div>
<script>
function reimprimirDevolucion() {
    var src = document.getElementById('iframeDevolucion').src;
    if (src) fetch(src).then(function(){}).catch(function(){});
}
</script>