//funcion para asignar los dias de credito del proveedor
    function diasCredito(select){            
        let dias = $(select).find('option:selected').attr('days_credit');            
        $('#plazo').val(dias);
    }

    //funcion para habilitar los campos de entrada
    function btnEditEntrada(btn){
        let row = btn.closest('tr');
        let input = row.querySelector('.entradas');
        let btnOk = row.querySelector('.btnOkEntrada');

        if($(input).length){
            $(input).removeAttr('disabled');
        }

        $(btn).addClass('d-none');
        $(btnOk).removeClass('d-none');
    }

    function btnOk(btn){
        let row = btn.closest('tr');
        let btnEdit = row.querySelector('.btnEdit');
        let input = row.querySelector('.entradas');

        if($(input).length){
            $(input).attr('disabled', true);
        }

        $(btn).addClass('d-none');
        $(btnEdit).removeClass('d-none');
    }

    //funcion click boton update
    function btnUpdate(){
        Swal.fire({
            title: "¿Seguro que quieres actualizar?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: "Actualizar",
            cancelButtonText: `Cancelar`,
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-secondary'
            },
            }).then((result) => {
            if (result.isConfirmed) {
                $('#formAction').submit();
            } 
        });
    }

    //funcion para hacer el calculo de totales en entradas al editar
    function entradaProduct(id, entrada, tipo_impuesto, impuesto){
        let precio_unitario = $('.precioUnitario-'+id).html();
        Livewire.dispatch('entradaProductEdit', [id, entrada, precio_unitario, tipo_impuesto, impuesto]);
    }

    //funcion para hacer el calculo de totales en entradas al editar
    function recibidoProduct(id, recibido, tipo_impuesto, impuesto){
        let precio_unitario = $('.precioUnitario-'+id).html();
        Livewire.dispatch('recibidoProduct', [id, recibido, precio_unitario, tipo_impuesto, impuesto]);
    }

    //funcion para preguntar cerrar la compra
    function btnCerrar(){
        Swal.fire({
            title: "¿Seguro que quieres cerrar la compra?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: "SI",
            cancelButtonText: `NO`,
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-secondary'
            },
            }).then((result) => {
            if (result.isConfirmed) {
                $('#formAction').submit();
            } 
        });            
    }

    //funcion para deshabilitar un detalle de compra
    function btnDestroyEntrada(detalle_id){   
        console.log(detalle_id);
        
        let url = compraDestroyRoute.replace(':id', detalle_id);
        Swal.fire({
            title: "¿Seguro que quieres eliminar el producto?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: "SI",
            cancelButtonText: `NO`,
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-secondary'
            },
            }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            } 
        }); 
    }

    function btnSolicitar(compra_id) {
    let fecha = $('#programacion_entrega').val();

    if (fecha != '') {
        let url = compraStatusRoute
            .replace(':id', compra_id)
            .replace(':status', 3);
        window.location.href = url;
    } else {
        Swal.fire('Para continuar es requerida la fecha de entrega.', '', 'info');
    }
}