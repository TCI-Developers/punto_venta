@extends('adminlte::page')
@section('title', 'Ventas')

@section('css')
    <style>
        .displayNone{
            display:none;
        }
    </style>
@stop
@section('js')
    @include('components..use.notification_success_error')
    @if ($errors->any())
    <script>
        $(document).ready(function() {
            Swal.fire({
                icon: 'info',
                title: 'Ocurrio un error',
                html: `
                    <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                `
            });
        });
    </script>
    @endif
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
        $('#change').val(change);
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
            getChange($('#total_sale').val());
        }
        $('#btnAddMov').fadeOut();
        $('#btnCobro').fadeOut();
        $('input[name=status]').val('cobro');
    }
</script>
@stop

@section('content')
        @livewireStyles
        @livewire('sales.sale', [$type, $id])
        @livewireScripts
@stop