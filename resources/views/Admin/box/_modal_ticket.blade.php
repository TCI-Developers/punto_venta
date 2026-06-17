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
      <div class="modal-body col-12" style="position:relative; min-height:70vh;">
          <div id="ticketSpinner" style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; background:#fff; z-index:10;">
              <div class="spinner-border text-primary" role="status" style="width:3rem; height:3rem;">
                  <span class="sr-only">Cargando...</span>
              </div>
              <p class="mt-3 text-muted">Generando ticket...</p>
          </div>
          <iframe id="ticketIframe" src="about:blank" title="Tickets"
              style="width:100%; height:70vh; opacity:0; transition: opacity 0.3s;"
              onload="if(this.src !== 'about:blank'){ document.getElementById('ticketSpinner').style.display='none'; this.style.opacity='1'; }">
          </iframe>
      </div>
    </div>
  </div>
</div>

<script>
    (function(){
        var ticketUrl = "{{ route('ticket.box', [Auth::User()->id, 'true']) }}";
        window.loadTicketBox = function(){
            var iframe = document.getElementById('ticketIframe');
            if(iframe && (iframe.src === 'about:blank' || iframe.src === '')){
                iframe.src = ticketUrl;
            }
        };
    })();
</script>