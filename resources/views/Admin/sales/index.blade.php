@extends('adminlte::page')

@section('title', 'Ventas')

@section('css')
    <style>
        .w-20{
            width:20% !important;
        }
        .w-15{
            width:15% !important;
        }
        .w-80{
            width:80% !important;
        }
    </style>
@stop

@section('js')
    @include('components..use.notification_success_error')
    <!-- <script src="{{asset('js/script_datatables.js')}}"></script> -->
    <script>
        $(document).ready(function(){
              //funcion de daterangepicker
            $('.reportrange').on('apply.daterangepicker', function(ev, picker) {
                let startDate = picker.startDate.format('YYYY-MM-DD');
                let endDate = picker.endDate.format('YYYY-MM-DD');
                let position = $(this).attr('position');
                
                $('input[name="startDate['+position+']"]').val(startDate);
                $('input[name="endDate['+position+']"').val(endDate);
                $('#reportrange-'+position+' span').html(startDate+ ' - ' + endDate);

                Livewire.dispatch('getDate', [startDate, endDate, position == 0 ? 'date':'updated_at']);
            });
        })

        //Funcion para crear venta
        function btnAdd(){
            $('#modal_create .inputModal').val('');
            $('#title').html('Crear');
            $('#btnAddEdit').html('Crear');
        }

        //funcion para darle valor por default a daterange
        window.addEventListener('daterangepicker', event => {
            $.each($('.inputReportrange'), function(index, item){
                $(item).val(event.detail[0][0]);
            });
            $('.reportrange span').html(moment(event.detail[0][0]).format('MM/DD/YYYY') + ' - ' + moment(event.detail[0][0]).format('MM/DD/YYYY'));
        });
    </script>
@stop

@section('content')
        @livewireStyles
        @livewire('sales.sale', [$type, $id])
        @livewireScripts
@stop