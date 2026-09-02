
        //funcion para cerrar modal
        function btnCloseModal(){
            $('#modal_money').fadeOut(function(){
                $('#tickets').find('td').html('').attr('style', 'background-color:none;');
                $('#coins').find('td').html('').attr('style', 'background-color:none;');
                $('#totalTickets').html('Total: $');
                $('#totalCoins').html('Total: $');
            });
        }

        //funcion para mostrar y ocultar detalles de el cierre de turno
        function clickTr(index){
            if($('#group-of-rows-'+index).hasClass('collapse')){
                $('#group-of-rows-'+index).removeClass('collapse');
            }else{
                $('#group-of-rows-'+index).addClass('collapse');
            }
        }

        //ciclo para llenar tabla de modal de dinero
        function setDenominaciones(data,tr){
            total = 0;
            $.each(tr.find('td'), function(index, item){
                let pos = $(item).attr('field');
                let val = $(item).attr('valor');
                $(item).html(data[pos]);
                if(data[pos]){
                    $(item).attr('style', 'background-color:#5db9e1b0;');
                    total += val*data[pos];

                }
            });
            return total;
        }

        //funcion para abrir modal y llenar la tabla
        // Nota: se checa event.detail[0].status (1=encontrado, 0=no encontrado),
        // NO data.status (que es el campo status del box: 0=abierto, 1=completado, 2=irregular)
        // para evitar falso negativo en turnos abiertos (status=0 en BD).
        window.addEventListener('openModalMoney', event => {
            let payload = event.detail[0];
            let data    = payload.box;
            if(payload.status && data){
                $('#totalTickets').html('Total: $ '+setDenominaciones(data, $('#tickets')));
                $('#totalCoins').html('Total: $ '+setDenominaciones(data, $('#coins')));
                $('#modal_money').fadeIn();
            }else{
                alert('Error inesperado. Actualiza la página e inténtalo de nuevo.');
            }
        })