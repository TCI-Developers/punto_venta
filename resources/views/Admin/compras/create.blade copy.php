@extends('adminlte::page')

@section('title')
    {{isset($compra) ? 'Actualizar compra':'Nueva compra'}}
@stop

@section('css')
    <style>
        .displayNone{
            display:none;
        }
    </style>
@stop

@section('js')
    @include('components..use.notification_success_error')

    <script>
        //funcion para refrescar los selectpicker
        window.addEventListener('selectRefresh', event => {  
            $('.selectpicker').selectpicker('refresh');
        });

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

            $(btn).fadeOut(function(){
                $(btnOk).fadeIn();
            })
        }

        //funcion click boton update
        function btnUpdate(){
            Swal.fire({
                title: "¿Seguro que quieres actualizar?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: "Actualizar",
                cancelButtonText: `Cancelar`
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
            console.log(recibido);
                       
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
                cancelButtonText: `NO`
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#formAction').submit();
                } 
            });            
        }

        //funcion para deshabilitar un detalle de compra
        function btnDestroyEntrada(detalle_id){            
            let url = "{{ route('compra.destroy', ["detalle_id"]) }}";
            Swal.fire({
                title: "¿Seguro que quieres eliminar el producto?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: "SI",
                cancelButtonText: `NO`
                }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url.replace('detalle_id', detalle_id);
                } 
            }); 
        }

        //funcion para solicitar
        function btnSolicitar(){                
                var url = "{{ route('compra.status', [$compra_id ?? null, 3]) }}";
                let fecha = $('#programacion_entrega').val();

                if(fecha != ''){
                    window.location.href = url;
                }else{
                    swal.fire('Para continuar es requerida la fecha de entrega.', '', 'info');
                }
                
            }
    </script>
@stop

@section('content')

        @livewireStyles
        @livewire('compras.compra', [$compra_id, $user])
        @livewireScripts

@stop