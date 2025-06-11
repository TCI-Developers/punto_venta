@extends('adminlte::page')
@section('title', 'Ventas')

@section('css')
    <style>
        .displayNone{
            display:none;
        }

        .table-dev{
            background-color: #00000050 !important;
        }
    </style>
@stop
@section('js')
    @include('components..use.notification_success_error')
    <script src="{{asset('js/script_datatables.js')}}"></script>

    <script>
        $(document).ready(function(){
            //select tipo de pago
            $('#type_payment').on('change', function(){
                if($(this).val() == 'tarjeta'){
                    $('.input_amounts').val('').attr('disabled', true);
                }else{
                    $('.input_amounts').attr('disabled', false);
                }
            });

            btnOrEnableDisable();
        })

        //funcion para agregar precios del producto seleccionado
        window.addEventListener('modal_detail', event => {
            $('#price').empty().attr('disabled', false);
            $('#presentation_id').empty().attr('disabled', false);
            // $('#presentation_id').empty().append(`<option value="new">Agregar Presentación</option>`).attr('disabled', false);

            $.each(event.detail[0].part_to_products, function(index, value){
                $('#presentation_id').append(`
                    <option value="${value.id}" price="${value.price}">${event.detail[0].arr_presentations[index].type} - ${event.detail[0].arr_presentations[index].description ?? ''}</option>
                `);
            });

            $('#taxes').html(event.detail[0].product.taxes);
            $('input[name=taxes]').val(event.detail[0].product.taxes);
            $('#iva_ieps').html(event.detail[0].product.amount_taxes);
            
            if(event.detail[0].product.taxes == "IE3"){
                $('#title_taxes').html('IEPS');
            }else if(event.detail[0].product.taxes == "IVA"){
                $('#title_taxes').html('IVA');
            }else{
                $('#title_taxes').html('Sin Impuesto');
            }
            $('.selectpicker').selectpicker('refresh');
        });

        //funcion para poner el precio en input
        function setPrice(select){
            let opt = $(select).find('option:selected');
            $('#price').val(parseFloat(opt.attr('price')));
            getAmount();
        }

        //funcion para calcular el monto
        function getAmount(){
            let cant = $('#cant').val() ?? 0;
            let price = $('#price').val() ?? 0;
            let iva_ieps = parseFloat($('#iva_ieps').html());
            
            let subtotal = cant*price;
            let total_taxes = subtotal*iva_ieps;
            let amount = (subtotal*iva_ieps)+subtotal;
            $('input[name=total_taxes]').val(total_taxes.toFixed(2));
            $('#total_taxes').html(total_taxes.toFixed(2));
            $('#subtotal').val(subtotal.toFixed(2));
            $('#amount').val(amount.toFixed(2));
        }

        //funcion para abrir modal para editar movimiento almacen
        window.addEventListener('modal_detail_update', event => {
            $('#formSale').attr('action', $('#formSale').attr('edit'));

            $('#modal_create').fadeIn();
            $('#titleSale').html('Actualizar');
            $('#btnSubmit').html('Actualizar');
            
            $('input[name=mov_sale_id]').val(event.detail[0].sale_detail.id);
            $("#product_id option[value='"+event.detail[0].sale_detail.get_part_to_product.product_id+"']").attr("selected", true);
            $('#cant').val(event.detail[0].sale_detail.cant);
            $('#price').val(event.detail[0].sale_detail.get_part_to_product.price);

            $('#presentation_id').empty().attr('disabled', false).selectpicker('refresh');
            $.each(event.detail[0].part_to_products, function(index, value){
                $('#presentation_id').append(`
                    <option value="${value.id}" price="${value.price}" ${event.detail[0].sale_detail.part_to_product_id == value.id ? 'selected':''}>
                    ${event.detail[0].arr_presentations[index].type} - ${event.detail[0].arr_presentations[index].description ?? ''}
                    </option>
                `);
            });

            $('#taxes').html(event.detail[0].sale_detail.get_part_to_product.get_product.taxes);
            $('input[name=taxes]').val(event.detail[0].sale_detail.get_part_to_product.get_product.taxes);
            let title_taxes = 'SYS';
            let total_taxes = 0;

            if(event.detail[0].sale_detail.get_part_to_product.get_product.taxes != 'SYS'){
                title_taxes = event.detail[0].sale_detail.get_part_to_product.get_product.taxes == 'IVA' ? 'IVA':'IEPS';
                total_taxes = event.detail[0].sale_detail.iva > 0 ? 
                            event.detail[0].sale_detail.iva:event.detail[0].sale_detail.ieps;
            }

            $('#title_taxes').html(title_taxes);
            $('#iva_ieps').html(event.detail[0].sale_detail.get_part_to_product.get_product.amount_taxes);
            $('#total_taxes').html(total_taxes);
            $('input[name=total_taxes]').val(total_taxes);

            $('#subtotal').val(event.detail[0].sale_detail.subtotal);
            $('#amount').val(event.detail[0].sale_detail.amount);

            $('.selectpicker').selectpicker('refresh');
            $('.selectpicker').selectpicker('refresh');
        });

        //funcion para abrir modal
        function btnOpenModal(){
            $('#modal_create').fadeIn();
        }

        //funcion para cerrar modal y dejar campos por default
        function closeModal(){
            $('#modal_create').fadeOut();
            $('#titleSale').html('Agregar');
            $('#btnSubmit').html('Agregar');
            $('#price').val('');
            $('#cant').val('');
            $('#title_taxes').html('');
            $('#iva_ieps').html('');
            $('#total_taxes').html('');
            $('#taxes').html('');
            $('#subtotal').val('');
            $('#amount').val('');
            $(".select_modal option").attr("selected", false);
            $('.selectpicker').selectpicker('refresh');
            $('#formSale').attr('action', $('#formSale').attr('store'));
        }

        //funcion para habilitar campos de venta para actualizar
        function editSale(){
            $('.input_sale').attr('disabled', false);
            $('.selectpicker').selectpicker('refresh');

            $('#btnEnableEdit').fadeOut(function(){
                $('#btnUpdateSale').fadeIn();
                $('#btnCancelSale').fadeIn();
            });
            $('#btnCobro').fadeOut();
            
        }

        //funcion para habilitar campos de venta para actualizar
        function cancelEditSale(){
            $('.input_sale').attr('disabled', true);
            $('.selectpicker').selectpicker('refresh');

            $('#btnUpdateSale').fadeOut(function(){
                $('#btnCobro').fadeIn();
                $('#btnEnableEdit').fadeIn();
            });
            $('#btnCancelSale').fadeOut();
            
            $('#div_amounts').fadeOut(function(){
                $('#btnEnableEdit').fadeIn();
                $('#btnAddMov').fadeIn();
                $('#btnCobro').fadeIn();

                $('#amount_received').val('');
                $('#change').val('');
                $('.input_amounts').attr('disabled', true);
            });
            $('#btnCancelSale').fadeOut();
            $('#btnAcept').fadeOut();
            $('input[name=status]').val('');
        }

        //funcion para obtener el cambio
        function getChange(amount_received){
            let total = $('#total_sale').val();
            let change = amount_received - total;
            $('#change').val(change.toFixed(2));
            if(change>=0){
                $('#btnAcept').fadeIn();
            }
        }

        //funcion boton de cobrar
        function cobrar(){            
            $('#btnEnableEdit').fadeOut(function(){
                $('#div_amounts').fadeIn();
                $('#btnCancelSale').fadeIn();
            });
            
            // $('#total_sale').attr('disabled', false);
            $('#amount_received').attr('readonly', false);

            if($('#type_payment').val() == 'tarjeta'){
                $('#amount_received').val($('#total_sale').val()).attr('readonly', true);
                getChange($('#total_sale').val().toFixed(2));
            }
            // $('#btnAddMov').fadeOut();
            $('#btnCobro').fadeOut();
            $('input[name=status]').val('cobro');
        }

        //fucnion para deshabilitar botones editar y eliminar de la tabla
        function btnOrEnableDisable(){
            let grupos = {};
            $("#tbody_details tr").each(function () {
                let clase = $(this).attr("ident");
                if (!grupos[clase]) {
                    grupos[clase] = [];
                }
                grupos[clase].push($(this));
            });
            
            $.each(grupos, function (clase, trs) {
                let total = trs.length;       
                if (total > 1) {
                for (let i = 0; i < total-1; i++) {
                        trs[i].find("button").prop("disabled", true);
                    }
                }
            });
        }
    </script>

    <script>
        window.addEventListener('scan', event => {      
            let sale_detail = event.detail[0].sales_detail;
            let cant_sales_detail = event.detail[0].cant_sales_detail;
            let product = event.detail[0].product;
            let presentation = event.detail[0].persentation;
            let promotions = event.detail[0].promotions;
            let unidad_sat = event.detail[0].unidad_sat;
            let tipo = event.detail[0].tipo;
            let total = 0.00;
            let descuento = 0.00;

            console.log('*', presentation);
            
            if(presentation.length > 0 && presentation[presentation.length - 1].stock < 0){
                swal.fire('Sin existencias en sistema.', '', 'info');
                Livewire.dispatch('stockOff', {'sale_detail_id' : sale_detail[sale_detail.length - 1].id,
                                                'code': presentation[presentation.length - 1].code_bar});
            }
            
            $('#tbody_details').empty();
            if(sale_detail.length){
                $('#btnCobro').fadeIn();
            }else{
                $('#btnCobro').fadeOut();
            }

            if(sale_detail.length == 0){
                $('#tbody_details').append(`
                    <tr id="trEmpty"><td colspan="10" class="table-warning text-center">Sin movimientos.</td></tr>
                `);
            }
            
            let total_descuento = 0;
            $.each(sale_detail, function(index, val){          
                let tipo_impuesto = '';
                let impuesto = 0.00;
                if(val.iva == 0 && val.ieps == 0){
                    tipo_impuesto = 'SYS';
                }else{
                    tipo_impuesto = val.iva != 0 ? 'IVA':'IEPS';
                    if(tipo_impuesto == 'IVA'){
                        impuesto = parseFloat(val.iva);
                    }else{
                        impuesto = parseFloat(val.ieps);
                    }
                }
                console.log('entra', cant_sales_detail.length);
                
                
                $.each(cant_sales_detail[index], function(contador, value){   
                    let subtotal_ = value.cant * val.unit_price;
                    let total_ = (subtotal_ - value.total_descuento) + impuesto;
                    total_descuento += value.total_descuento;
                    total += total_;
                    
                    $('#tbody_details').append(`
                    <tr class="text-center" ident="tr-${product[index]['code_product']}">
                        <td>${product[index]['code_product']}</td>
                        <td class="text-center">${value.cant}</td>
                        <td class="text-center">${unidad_sat[index]}</td>
                        <td class="text-center">${tipo_impuesto}</td>
                        <td class="text-center">$ ${number_format(val.unit_price)}</td>
                        <td class="text-center">$ ${impuesto}</td>
                        <td class="text-center">$ ${number_format(subtotal_)}</td>
                        <td class="text-center">$ ${number_format(value.total_descuento)}</td>
                        <td class="text-center">$ ${number_format(total_)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-info btn-sm" onClick="btnCantProduct(${presentation[index]['id']})"><i class="fa fa-plus"></i></button>
                            <button type="button" class="btn btn-danger btn-sm" onClick="btnDestroyProduct(${value.id})"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                    `);
                });
            });           
            
            $('#tbody_total').empty().append(`
                <tr class="table-info"><td colspan="6"></td>
                    <td class="text-right text-bold">Totales</td>
                    <td class="text-center" >$ <span class="badge badge-success">${number_format(total_descuento)}</span></td>
                    <td class="text-center text-bold" >$ <span>${number_format(total)}</span></td>
                    <td></td>
                </tr>
            `);
            
            $('#presentation_id').val('').focus();
            $('#update_cant_prod').val('');
            $('#update_sale_detail_id').val('');
            $('#modal_cant').fadeOut();
            $('#total_sale').val((total - descuento).toFixed(2));

            if(tipo == 'destroy'){
                Swal.fire('Producto eliminado con exito.', '', 'success');
            }

            btnOrEnableDisable();
        });

        //funcion para mostrar alerta de stock
        window.addEventListener('alert', event => {   
            swal.fire(event.detail[0].message, '', 'info');
        });

        //funcion para modificar cantidad de productos registrados
        function btnCantProduct(presentation_id){
            $('#presentation_id').val(presentation_id);
            $('#modal_cant').fadeIn();
        }

        //funcion para actualizar cantidad de producto
        function updateCant(){
            let presentation_id = $('#presentation_id').val();
            let cant = $('#update_cant_prod').val();
            
            Livewire.dispatch('updateCant', {'presentation_id' : presentation_id, 'cant' : cant});
        }

        //funcion para formatear numeros
        function number_format(number){
            let value = (number).toLocaleString(
            undefined,
            { minimumFractionDigits: 2 }
            );
            return value;
        }

        //funcion para eliminar detalle de venta
        function btnDestroyProduct(sale_detail_cant_id){
            Swal.fire({
                icon: 'question',
                title: "¿Deseas eliminar este producto?",
                showCancelButton: true,
                confirmButtonText: "Aceptar",
                cancelButtonText: "Cancelar",
                }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('destroyProduct', {'sale_detail_cant_id' : sale_detail_cant_id});
                } 
            });
        }

        //funcion para cerrar modal de cantidades
        function btnCancelModal(){
            $('#modal_cant').fadeOut();
        }
    </script>
@stop

@section('content')
        @livewireStyles
        @livewire('sales.sale', [$type, $id]) 
        @livewireScripts
@stop