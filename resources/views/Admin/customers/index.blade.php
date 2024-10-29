@extends('adminlte::page')

@section('title', 'Clientes')

@section('js')
    @include('components..use.notification_success_error')
    <script src="{{asset('js/script_datatables.js')}}"></script>

    <script>
        //funcion para mostrar modal
        function showModal(){
            $('#modal_customer').fadeIn();
        }

        //funcion para cancelar en modal
        function btnCancel(){
            $('#modal_customer').fadeOut();
            $('.inputModal').val();
            $('.title').html('Agregar');
            $('input[name=id]').val('').attr('disabled', true);
        }

        //funcion para abrir modal edit
        window.addEventListener('showModalEdit', event => {
            $('#modal_customer').fadeIn();
            $('.title').html('Actualizar');
            $('input[name=id]').val(event.detail[0].customer.id).attr('disabled', false);
            $('#name').val(event.detail[0].customer.name);
            $('#razon_social').val(event.detail[0].customer.razon_social);
            $('#rfc').val(event.detail[0].customer.rfc);
            $('#postal_code').val(event.detail[0].customer.postal_code);
            $('#regimen_fiscal').val(event.detail[0].customer.regimen_fiscal);
        })
    </script>
@stop

@section('content')
        @livewireStyles
        @livewire('customers.customer')
        @livewireScripts
@stop