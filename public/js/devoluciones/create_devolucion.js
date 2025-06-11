//funcion para abrir modal
    function showModal(detail_cant_id, part_to_product_id, iva, ieps){
        let cant = $('#tr-'+detail_cant_id+' .cant').html(); 
        
        $('#detail_cant_id').val(detail_cant_id);               
        
        if(!$('#input-'+detail_cant_id+'-part_to_product_id').length){
            $('#formStore').append(`
                <input type="hidden" id="input-${detail_cant_id}-part_to_product_id" name="part_to_product_id[]" value="${part_to_product_id}">
                <input type="hidden" id="input-${detail_cant_id}-iva" name="iva[]" value="${iva}">
                <input type="hidden" id="input-${detail_cant_id}-ieps" name="ieps[]" value="${ieps}">
            `);
        }else{
            $('#input-'+detail_cant_id+'-part_to_product_id').val(part_to_product_id);
            $('#input-'+detail_cant_id+'-iva').val(iva);
            $('#input-'+detail_cant_id+'-ieps').val(ieps);
        }

        $('#cant').attr('max', parseFloat(cant));
        $('#modal_cant').show();
    }

    //funcion para cerrar modal
    function btnCancelModal(){
        $('#cant').removeAttr('max').val('');
        $('#modal_cant').hide();
    }

    //funcion para cerrar modal
    function devolucionCant(){
        if($('#td_dev').length){
            $('.tbody_dev').empty();
        }

        let detail_cant_id = $('#detail_cant_id').val(); //id detalle venta
        let data = [];

        data['detail_cant_id'] = detail_cant_id;
        data['cant'] = parseFloat($('#cant').val()); //cantidad a devolver
            data['cantidad_sale'] = parseFloat($('#tr-'+detail_cant_id+' .cant').html()); //total cantidad detalle venta
            data['code_product'] = $('#tr-'+detail_cant_id+' .code_product').html(); //codigo producto
            data['tipo_impuesto'] = $('#tr-'+detail_cant_id+' .tipo_impuesto').html(); //tipo impuesto
            data['unit_price'] = $('#tr-'+detail_cant_id+' .unit_price').html();  // precio unitario
                data['unit_price'] = parseFloat(data['unit_price'].replace('$','')); //precio unitario parseado
            data['amount_impuesto'] = $('#tr-'+detail_cant_id+' .tipo_impuesto').attr('val'); //monto impuesto de producto
            data['subtotal'] = parseFloat(data['cant']) * data['unit_price']; //subtotal
            data['total_impuestos'] = data['subtotal'] * parseFloat(data['amount_impuesto']); //total impuestos
            data['descuento'] = $('#tr-'+detail_cant_id+' .descuento').attr('val'); //descuento presentacion
                data['descuento'] = parseFloat(data['descuento'].replace('$','')); //descuento parseado
            data['total_descuento'] = data['cant'] * data['descuento']; //total descuento

        data['total_devolucion'] = (data['subtotal'] - data['total_descuento']) + data['total_impuestos']; //total devolucion

        if(data['cant'] <= data['cantidad_sale'] && !$('#tr_dev-'+detail_cant_id).length){//no puedes ingresar una cantidad mayo a la venta
            $('.tbody_dev').append(`
                <tr class="text-center" id="tr_dev-${detail_cant_id}">
                    ${ tdTable(data) }
                </tr>
            `);
            $('.d-none').removeClass('d-none');
            setData(data);
            btnCancelModal();
        }else if(data['cant'] <= data['cantidad_sale'] && $('#tr_dev-'+detail_cant_id).length){
            $('#tr_dev-'+detail_cant_id).empty().append(`
                ${ tdTable(data) }
            `);
            $('.d-none').removeClass('d-none');
            setData(data);
            btnCancelModal();
        }else{
            alert();
        }
    }

    //funcion para hacer submit a form
    function buttonSubmit(){
        $('#formStore').submit();
    }

    //mostramos alerta
    function alert(){
        Swal.fire('La cantidad ingresada es mayor a la de la venta.', '', 'info');
    }

    //funcion creamos los td que se ingresaran
    function tdTable(data){
        return `
                <td>${ data['code_product'] }</td>
                <td>${ data['cant'] }</td>
                <td>${ data['tipo_impuesto'] }</td>
                <td>$${ formatNumber(data['unit_price']) }</td>
                <td>$${ formatNumber(data['total_impuestos']) }</td>
                <td>$${ formatNumber(data['subtotal']) }</td>
                <td>$${ formatNumber(data['total_descuento']) }</td>
                <td>$${ formatNumber(data['total_devolucion']) }</td>
        `;
    }

    //funcion para llenar todos los inputs con la data
    function setData(data){
        let iva = $('#input-'+data['detail_cant_id']+'-iva').val();
        let ieps = $('#input-'+data['detail_cant_id']+'-ieps').val();
        let total = parseFloat(data['subtotal']) + parseFloat(iva) + parseFloat(ieps);
        
        if(!$('#input-'+data['detail_cant_id']+'-subtotal').length){
        $('#formStore').append(`
            <input type="hidden" id="input-${data['detail_cant_id']}-subtotal" name="subtotal[]" value="${data['subtotal']}">
            <input type="hidden" id="input-${data['detail_cant_id']}-unit_price" name="unit_price[]" value="${data['unit_price']}">
            <input type="hidden" id="input-${data['detail_cant_id']}-total" name="total[]" value="${total}">
            <input type="hidden" id="input-${data['detail_cant_id']}-cant" name="cant[]" value="${data['cant']}">
            <input type="hidden" id="input-${data['detail_cant_id']}-descuento" name="descuento[]" value="${data['descuento']}">
            <input type="hidden" id="input-${data['detail_cant_id']}-total-descuento" name="total_descuento[]" value="${data['total_descuento']}">
        `);
        }else{
            $('#input-'+data['detail_cant_id']+'-subtotal').val(data['subtotal']);
            $('#input-'+data['detail_cant_id']+'-unit_price').val(data['unit_price']);
            $('#input-'+data['detail_cant_id']+'-total').val(total);
            $('#input-'+data['detail_cant_id']+'-cant').val(data['cant']);
            $('#input-'+data['detail_cant_id']+'-descuento').val(data['descuento']);
            $('#input-'+data['detail_cant_id']+'-total-descuento').val(data['total_descuento']);
        }
    }

    //funcion para formatear el numero
    function formatNumber(num) {
        return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
    }

    //funcion para habilitar la edicion (solo admin)
    function editDevolucion(button){
        if($(button).hasClass('btn-warning')){ //edit
            $(button).removeClass('btn-warning').addClass('btn-danger').empty().append('<i class="fa fa-times"></i> Cancelar');
            $('.showEdit').attr('readonly', false).removeClass('d-none');
            $('.showEditread').attr('readonly', false);
        }else{ //cancelar edit
            $(button).removeClass('btn-danger').addClass('btn-warning').empty().append('<i class="fa fa-edit"></i> Editar');
            $('.showEdit').attr('readonly', true).addClass('d-none');
            $('.showEditread').attr('readonly', true);
        }
    }