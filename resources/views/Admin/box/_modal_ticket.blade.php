<!-- Modal -->
<div class="modal modalTicket" id="modalTicket" tabindex="-1" aria-labelledby="modalTicketLabel" 
  aria-hidden="true" data-backdrop="static" data-keyboard="false" 
   wire:ignore>
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTicketLabel">Ticket</h5>
        <a href="{{route('box.statusBox')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Regresar</a>
        <a href="{{route('box.statusBox')}}" class="btn btn-primary btn-sm"><i class="fa fa-check"></i> Cerrar Turno</a>
      </div>
      <div class="modal-body col-12">
          <iframe src="http://127.0.0.1:8100/ticket-box/{{Auth::User()->id}}" title="Tickets" style="width:100%; height:70vh;"></iframe>
      </div>
    </div>
  </div>
</div>