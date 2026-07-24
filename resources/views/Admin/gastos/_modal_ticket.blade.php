<!-- Modal -->
<div class="modal modalTicket" id="modalTicket" tabindex="-1" aria-labelledby="modalTicketLabel"
  aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTicketLabel">Ticket de gasto</h5>
        <button type="button" class="btn btn-secondary btn-sm" onclick="$('#modalTicket').hide();"><i class="fa fa-times"></i> Cerrar</button>
      </div>
      <div class="modal-body col-12" style="position:relative; min-height:70vh;">
          <div id="ticketSpinner" style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; background:#fff; z-index:10;">
              <div class="spinner-border text-primary" role="status" style="width:3rem; height:3rem;">
                  <span class="sr-only">Cargando...</span>
              </div>
              <p class="mt-3 text-muted">Generando ticket...</p>
          </div>
          <iframe id="ticketIframe" src="about:blank" title="Ticket de gasto"
              style="width:100%; height:70vh; opacity:0; transition: opacity 0.3s;"
              onload="if(this.src !== 'about:blank'){ document.getElementById('ticketSpinner').style.display='none'; this.style.opacity='1'; }">
          </iframe>
      </div>
    </div>
  </div>
</div>

<script>
    (function(){
        var ticketUrlTemplate = "{{ route('ticket.gasto', [0, 'true']) }}";
        window.loadTicketGasto = function(gastoId){
            var iframe = document.getElementById('ticketIframe');
            document.getElementById('ticketSpinner').style.display = 'flex';
            iframe.style.opacity = '0';
            iframe.src = ticketUrlTemplate.replace('/0/', '/'+gastoId+'/');
            $('#modalTicket').show();
        };
    })();
</script>
