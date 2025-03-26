@extends('adminlte::page')

@section('title')
    {{isset($compra) ? 'Actualizar compra':'Nueva compra'}}
@stop

@section('js')
    @include('components..use.notification_success_error')

    <script>
        //funcion para refrescar los selectpicker
        window.addEventListener('selectRefresh', event => {  
            $('.selectpicker').selectpicker('refresh');
        });
    </script>
@stop

@section('content')

        @livewireStyles
        @livewire('compras.compra', [$compra_id, $user])
        @livewireScripts

@stop