<!-- Modal -->
<div class="modal modalTicket" id="modalTicket" tabindex="-1" aria-labelledby="modalTicketLabel" 
  aria-hidden="true" data-backdrop="static" data-keyboard="false" 
   wire:ignore>
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTicketLabel">Ticket</h5>
        <a href="{{route('devoluciones.index')}}" class="btn btn-primary btn-sm"><i class="fa fa-check"></i> Cerrar</a>
      </div>
      <div class="modal-body col-12">
          <iframe src="http://127.0.0.1:8100/ticket-devolution/{{$devolution->id ?? 0}}/true" title="Tickets" style="width:100%; height:70vh;"></iframe>
      </div>
    </div>
  </div>
</div>