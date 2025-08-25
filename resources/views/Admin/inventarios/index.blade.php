<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucursal</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
</head>
<body>
    <main class="content">
    @include('components.use.nav-slider')
    @include('components.use.notification_success_error')
        @livewireStyles
        @livewire('inventarios.inventario')
        @livewireScripts
    </main>   

    <script>
        //con esto le damos fotmato al select para buscar y seleccion multiple
        document.addEventListener('DOMContentLoaded', function () {            
            const element = document.getElementById('linea_id');
            if (element) {
                new Choices(element, {
                    removeItemButton: true,
                    placeholder: true,
                    placeholderValue: 'Seleccione una linea...',
                    searchEnabled: true,
                    shouldSort: false,
                });

            }
        });

        //muestra los campos de stock y acciones
        window.addEventListener('showInputs', event => {  
            let status = event.detail[0].status;
            console.log('status', status);
            
            if(status == 1){
                $('.showInputs').addClass('d-none');
                $('.new_stock').val('').attr('disabled', true);
            }else{
                $('.showInputs').removeClass('d-none');
            }
        });

        //asignamos valores al select despues de recargar la vista y se quedaran productos bloqueados
        window.addEventListener('selectAddLines', event => {  
            console.log(event.detail[0].lines);
            let ids = event.detail[0].lines;
            const select = document.getElementById('linea_id');

            for (let option of select.options) {
                option.selected = ids.includes(parseInt(option.value));
            }

            $('#')
        });

        //muestra alerta de completado
        window.addEventListener('alert', event => {  
            status = event.detail[0].status;
            message = event.detail[0].message;

            Swal.fire(message, '', status);    
        });

            //pintamos el body de la tabla
        window.addEventListener('refreshTable', event => {  
            item = event.detail[0].products;
            lineas = event.detail[0].lineas;
            status = event.detail[0].status;
            console.log('^', lineas.length, status);
            
            $('#bodyTable').empty();
            $.each(item, function(index, val){
                let existence = parseFloat(val.existence);                
                $('#bodyTable').append(
                   `<tr>
                        <td>${val.code_product}</td>
                        <td class="text-center">${val.get_brand.name}</td>
                        <td class="text-right">${existence.toFixed(2)}</td>
                        <td class="showInputs ${(lineas.length && status == 0) ? '':'d-none'}">
                            <input type="number" class="form-control new_stock" name="new_stock[]" id="new_stock-${val.id}" wire:model.defer="inputsNewStock.${val.id}" placeholder="0.00" disabled>
                        </td>
                        <td class="showInputs ${(lineas.length && status == 0) ? '':'d-none'} text-center">
                            <button type="button" class="btn btn-warning btn-sm" onclick="enableOrDisabledInput(${val.id}, 'enabled')" ${status == 1 ? 'disabled':''}><i class="fa fa-check"></i></button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="enableOrDisabledInput(${val.id}, 'disabled')" ${status == 1 ? 'disabled':''}><i class="fa fa-times"></i></button>
                        </td>
                    </tr>`);          
            });
        });
        
        //funcion para habilitar o desabilitar el input de stock
       function enableOrDisabledInput(id, type){        
            if(type == 'enabled'){
                $('#new_stock-'+id).removeAttr('disabled', 'false');
            }else{
                $('#new_stock-'+id).attr('disabled', 'true');
            }
       }
    </script>
</body>
</html>
