<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')

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

    <script>
       document.addEventListener('DOMContentLoaded', function () {
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.querySelector('input[name="reportrange"]');
            if (input) {
                new daterangepicker(input, {
                    opens: 'left'
                }, function (start, end, label) {
                    console.log("A new date selection was made: " +
                        start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                });
            }
        });
    </script>


</head>
<body>
    <main class="content">
        @include('components.use.nav-slider')
        @include('components.use.notification_success_error')

        @livewireStyles
        @livewire('sales.sale', [$type, $id])
        @livewireScripts
    </main>   
</body>
</html>