@extends('adminlte::page')

@section('title', 'Productos')

@section('css')
    <style>
        .displayNone{
            display:none;
        }
    </style>
@stop

@section('js')
@include('components..use.notification_success_error')
    <script src="{{asset('js/script_datatables.js')}}"></script>

    <script>
        $(document).ready(function(){
            //funcion para mostrar presentaciones eliminados de productos ya relacionados
            $('.showPresentationsDelete').on('click', function(){
                let product_id = $('input[name=id]').val();
                let status = $(this).attr('status');
                let status_modal = $('input[name=status_modal]').val();
                if(status == 1){
                    $(this).fadeOut(function(){
                        $('#btnPresentations-0').fadeIn();
                    });
                }else{
                    $(this).fadeOut(function(){
                        $('#btnPresentations-1').fadeIn();
                    });
                }
                Livewire.dispatch('value_product_id', [product_id, status]);
            });

            //funcion par mostrar form para crear presentacion
            $('#presentation_type_id').on('change', function(){
                if($(this).val() == 'new'){
                    $('#form_add_presentation_product').fadeOut(function(){
                        $('#formPresentations').fadeIn();
                    })

                    $('#modal_presentationLabel').fadeOut(function(){
                        $('#title_form_presentation').fadeIn();
                    });
                    $('#divButtons').fadeOut();
                    $('#form_add_presentation_product').fadeOut(function(){
                        $('#formPresentations').fadeIn();
                    });
                }
            });
                
        })

        //funcion para abrir modal 
        function modalAddPresentation(product_id){
            Livewire.dispatch('value_product_id', [product_id, 1]);
            $('#title_modal').html($('#tr-'+product_id+' .code_product').html());
            $('input[name=id]').val(product_id);
            $('#modal_presentation').fadeIn();
        }

        //funcion para boton cancelar actualizacion
        function cancelUpdate(update){
            $('#btnCancelUpdate').fadeOut();
            $('#presentation_type_id').val('').selectpicker('refresh');
            $('#price').val('');
            $('#code_bar').val('');

            $('#tipo_descuento').val('monto').selectpicker('refresh');
            $('#vigencia_cantidad_fecha').val('fecha').selectpicker('refresh');

            $('#monto_porcentaje').val('').selectpicker('refresh');
            $('#vigencia_cantidad').val('').selectpicker('refresh');
            $('#vigencia_fecha').val('');
            $('#title_monto_porcentaje').html('Monto');
            $('#basic-addon1').html('$');
            $('#title_vigencia').html('Fecha');
            $('#vigencia_cantidad').fadeOut(function(){
                $('#vigencia_fecha').fadeIn();
            });

            $('#titleButton').html('Asignar');
            $('#title').html('Asignar');
            Livewire.dispatch('value_product_id', [$('input[name=id]').val(), 1]);
            if(update){
            swal.fire('Actualización cancelada.', '', 'success');
            }
        }

        //funcion para cerrar modal de cancelar
        function cancelModal(){
            cancelUpdate(0);
            console.log('ebtra');
            $('#modal_presentation').fadeOut();
        }

        //funcion para generar boton de registros
        function createBtn(id, status){
            let btn = status == 1 ?
                    `<button type="button" class="btn btn-light btn-sm" wire:click="deletePresentation(${id})"><img src="{{asset('icons/trash.svg')}}" alt="icon trash"></button>`:
                    `<button type="button" class="btn btn-success btn-sm" wire:click="enablePresentation(${id})"><img src="{{asset('icons/update.svg')}}" alt="icon update"></button>`;

            return btn;
        }

        //funcion cancelar en form de crear presentacion
        function cancelPresentation(){
            $('#title_form_presentation').fadeOut(function(){
                $('#modal_presentationLabel').fadeIn();
            });
            $('#divButtons').fadeIn();
            $('#formPresentations').fadeOut(function(){
                $('#form_add_presentation_product').fadeIn();
            });
            $('#type').val('');
            $('#description').val('');
            $('#unidad_sat_id').val('').selectpicker('refresh');
            $('#presentation_type_id').val('').selectpicker('refresh');
        }

        //funcion para esconder o mostrar descuentos
        function showOrHideDescuentos(type){
            if(type=='show'){
                $('#btnShow').fadeOut(function(){
                    $('#btnHide').fadeIn();
                });
                $('#div_descuento').fadeIn();  
            }else{
                $('#btnHide').fadeOut(function(){
                    $('#btnShow').fadeIn();
                });
                $('#div_descuento').fadeOut();  
            }
        }

        //funcion para mostrar input de vigencia dependiendo lo que seeleccione
        function selectsDescuento(option){
            if(option == 'monto'){
                $('#title_monto_porcentaje').html('Monto');
                $('#basic-addon1').html('$');
            }else if(option == 'porcentaje'){
                $('#title_monto_porcentaje').html('Porcentaje');
                $('#basic-addon1').html('%');
            }

            if(option == 'cantidad'){
                $('#title_vigencia').html('Cantidad');
                $('#vigencia_fecha').fadeOut(function(){
                    $('#vigencia_cantidad').fadeIn();
                });
            }else if(option == 'fecha'){
                $('#title_vigencia').html('Fecha');
                $('#vigencia_cantidad').fadeOut(function(){
                    $('#vigencia_fecha').fadeIn();
                });
                
            }
        }
    </script>

    <!-- script fucniones livewire -->
    <script>
        //muestra el listado de presentaciones relacionadas con el producto
        window.addEventListener('table_modal', event => {
            $('#body_table').empty();

            if(event.detail[0].part_to_products.length){
                $.each(event.detail[0].part_to_products, function(index, item){
                    let descuento  = 'N/A';
                    if(item.monto_porcentaje > 0){
                        descuento = item.tipo_descuento == 'monto' ? '$ '+item.monto_porcentaje:'% '+item.monto_porcentaje;

                    }
                    $('#body_table').append(`
                        <tr>
                            <td>${event.detail[0].presentation_name[index]}</td>
                            <td class="text-center">$ ${item.price}</td>
                            <td class="text-center">${descuento}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning btn-sm" wire:click="showPresentation(${item.id})"><img src="{{asset('icons/edit.svg')}}" alt="icon edit"></button>
                                ${createBtn(item.id, event.detail[0].status)}
                            </td>
                        </tr>
                    `);
                });
            }else{
                $('#body_table').append(`
                        <tr>
                            <tr><td colspan="4" class="table-warning text-center">Sin presentaciones.</td></tr>
                        </tr>
                    `);
            }

        });

        //muestra los campos para editar presentacion de producto
        window.addEventListener('showModalEdit', event => {
            $('#btnCancelUpdate').fadeIn();
            $('#presentation_type_id').val(event.detail[0].part_to_product.presentation_product_id).selectpicker('refresh');
            console.log('event', event.detail[0]);
            
            $('#titleButton').html('Actualizar');
            $('#title').html('Actualizar');
            $('.inputModal').removeClass('border-danger');

            
            $('#title_monto_porcentaje').html(event.detail[0].part_to_product.tipo_descuento == 'porcentaje' ? 'Porcentaje':'Monto');
            $('#monto_porcentaje').val(event.detail[0].part_to_product.monto_porcentaje);
            $('#basic-addon1').html(event.detail[0].part_to_product.tipo_descuento == 'porcentaje' ? '%':'$');
            $('#tipo_descuento').val(event.detail[0].part_to_product.tipo_descuento).selectpicker('refresh');
            
            $('#vigencia_cantidad_fecha').val(event.detail[0].part_to_product.vigencia_cantidad_fecha).selectpicker('refresh');
            $('#title_vigencia').html(event.detail[0].part_to_product.vigencia_cantidad_fecha == 'fecha' ? 'Fecha':'Cantidad');
            
            if(event.detail[0].part_to_product.vigencia_cantidad_fecha == 'fecha'){
                $('#vigencia_cantidad').fadeOut(function(){
                    $('#vigencia_fecha').val(event.detail[0].part_to_product.vigencia).fadeIn();
                });
            }else{
                $('#vigencia_fecha').fadeOut(function(){
                    $('#vigencia_cantidad').val(event.detail[0].part_to_product.vigencia).fadeIn();
                });
            }
            
            swal.fire('Actualizar presentación de producto', '', 'info');
        });

        //muestra notificacion por error o por carga correcta
        window.addEventListener('alert', event => {
            console.log(event.detail[0].type);
            if(event.detail[0].type == 'error'){
                $('.inputModal').removeClass('border-danger');
                $.each(event.detail[0].input, function(index, item){
                    // console.log('*', item);
                    $('#'+item).addClass('border-danger');
                })
                swal.fire('Campos incompletos', '', 'warning');
            }else if(event.detail[0].type == 'delete'){
                swal.fire('Se elimino con exito.', '', 'success');
                cancelUpdate(0);
            }else if(event.detail[0].type == 'update'){
                swal.fire('Presentación actualizada con exito.', '', 'success');
                cancelUpdate(0);
            }else{
                swal.fire('Presentación asignada con exito.', '', 'success');
                cancelUpdate(0);
            }
        });
    </script>
@stop

@section('content')
        @livewireStyles
        @livewire('products.product')
        @livewireScripts
@stop