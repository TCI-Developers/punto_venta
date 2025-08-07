        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('searchInput');
            const currentIndex = -1;

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    alert('Se presionó Enter en el campo de búsqueda');
                }
            });

            document.addEventListener('keydown', function (e) {
                const buttons = Array.from(document.querySelectorAll('.select-button'));

                // Si no hay resultados, salimos
                if (buttons.length === 0) return;

                // Elimina el resaltado actual
                buttons.forEach(btn => {
                    const row = btn.closest('tr');
                    if (row) row.classList.remove('highlighted-row');
                });

                // Flecha abajo
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    currentIndex = (currentIndex + 1) % buttons.length;
                    buttons[currentIndex].focus();
                }

                // Flecha arriba
                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    currentIndex = (currentIndex - 1 + buttons.length) % buttons.length;
                    buttons[currentIndex].focus();
                }

                // Resalta la fila del botón enfocado
                const currentButton = buttons[currentIndex];
                if (currentButton) {
                    const currentRow = currentButton.closest('tr');
                    if (currentRow) currentRow.classList.add('highlighted-row');
                }

                // Enter para hacer click
                if (e.key === 'Enter') {
                    const active = document.activeElement;
                    if (active && active.classList.contains('select-button')) {
                        e.preventDefault();
                        active.click();
                    }
                }

                // Si escriben de nuevo en el input, reiniciar el index
                if (document.activeElement === input && e.key.length === 1) {
                    currentIndex = -1;
                }
            });

            //cerramos modal y limpiamos con tecla esc
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && $('#modal_products').css('display') === 'block') {
                    $('#modal_products').hide();
                    $('#searchInput').val('');
                    $('#body_products').empty();
                    currentIndex = -1;
                }
            });

            //select tipo de pago
            $('#type_payment').on('change', function(){
                if($(this).val() == 'tarjeta'){
                    $('.input_amounts').val('').attr('disabled', true);
                }else{
                    $('.input_amounts').attr('disabled', false);
                }
            });

            btnOrEnableDisable();

            let table = $('#tbody_details').find('tr');
            if(!table.attr('id') === 'trEmpty' || table.attr('id') === undefined){
                $('#btnCobro').removeClass('d-none');
            }

            $('#formSale').on('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            });
            
        })

        //funcion para habilitar campos de venta para actualizar
        function editSale(){
            $('.input_sale').attr('disabled', false);

            $('#btnEnableEdit').addClass('d-none');
            $('#btnUpdateSale').removeClass('d-none');
            $('#btnCancelSale').removeClass('d-none');
            $('#btnCobro').addClass('d-none');
            
        } 

        //funcion para habilitar campos de venta para actualizar
        function cancelEditSale(){
            $('.input_sale').attr('readonly', true);

            $('#btnUpdateSale').addClass('d-none');
            $('#btnCobro').removeClass('d-none');
            $('#btnEnableEdit').removeClass('d-none');
            $('#btnCancelSale').addClass('d-none');
            
            $('#div_amounts').addClass('d-none');
                $('#btnEnableEdit').removeClass('d-none');
                $('#btnAddMov').removeClass('d-none');
                $('#btnCobro').removeClass('d-none');

                $('#amount_received').val('');
                $('#change').val('');
                $('.input_amounts').attr('readonly', true);

            $('#btnCancelSale').addClass('d-none');
            $('#btnAcept').addClass('d-none');
            $('input[name=status]').val('');
        }

        //funcion para seelccionar automatico el tipo de pago
        function metodoPago(){
            let option = $('#payment_method_id').find('option:selected').data('name')
            if(option === 'PPD'){
                $('#type_payment').val('tarjeta');
            }else{
                $('#type_payment').val('efectivo');
            }
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
            
            // if(presentation.length > 0 && presentation[presentation.length - 1].stock < 0){
            //     Swal.fire('Sin existencias en sistema.', '', 'info');
            //     Livewire.dispatch('stockOff', {'sale_detail_id' : sale_detail[sale_detail.length - 1].id,
            //                                     'code': presentation[presentation.length - 1].code_bar});
            // }
            
            $('#tbody_details').empty();
            if(sale_detail.length){
                $('#btnCobro').removeClass('d-none');
            }else{
                $('#btnCobro').addClass('d-none');
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
            $('#modal_cant').hide();
            $('#total_sale').val((total - descuento).toFixed(2));

            if(tipo == 'destroy'){
                Swal.fire('Producto eliminado con exito.', '', 'success');
            }

            $('#modal_products').hide();
            currentIndex = -1;
            btnOrEnableDisable();
        });

        window.addEventListener('alert', event => {
            if(event.detail[0].stock_0 == true){
                Swal.fire('Sin existencias en sistema.', '', 'info');
            }
        });

        //funcion para modificar cantidad de productos registrados
        function btnCantProduct(presentation_id){           
            $('#presentation_id').val(presentation_id);
            $('#modal_cant').show();
        }

        //funcion para actualizar cantidad de producto
        function updateCant(){
            let presentation_id = $('#presentation_id').val();
            let cant = $('#update_cant_prod').val();
            let aux = esNumero(cant);
            
            if(aux && cant>0){
                Livewire.dispatch('updateCant', {'presentation_id' : presentation_id, 'cant' : cant});
            }else if(!aux){
                Swal.fire('Solo se permiten valores numericos','','info');
            }else{
                Swal.fire('No se permiten valores negativos','','info');
            }
            
        }

        //funcion para eliminar detalle de venta
        function btnDestroyProduct(sale_detail_cant_id){
            Swal.fire({
                icon: 'question',
                title: "¿Deseas eliminar este producto?",
                showCancelButton: true,
                confirmButtonText: "Aceptar",
                cancelButtonText: "Cancelar",
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-secondary'
                },
                }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('destroyProduct', {'sale_detail_cant_id' : sale_detail_cant_id});
                } 
            });
        }

        //funcion para cerrar modal de cantidades
        function btnCancelModal(){
            $('#modal_cant').hide();
            $('#update_cant_prod').val('');
        }

        //funcion para formatear numeros
        function number_format(number){
            let value = (number).toLocaleString(
            undefined,
            { minimumFractionDigits: 2 }
            );
            return value;
        }

        //funcion para abrir modal de productos
        function modalProductos(type = 'show'){                     
            if(type == 'show'){
                $('#modal_products').show();
                $('#searchInput').focus().val('');
                $('#body_products').empty();
            }else{
                $('#modal_products').hide();
                $('#searchInput').val('');
                $('#body_products').empty();
            }   
        }

        //funcion boton de cobrar
        function cobrar(){            
            $('#btnEnableEdit').addClass('d-none')
            $('#div_amounts').removeClass('d-none');
            $('#btnCancelSale').removeClass('d-none');
            
            $('#amount_received').attr('readonly', false);
            
            if($('#payment_method_id option:selected').data('name') == 'PPD'){
                $('#amount_received').val($('#total_sale').val()).attr('readonly', true);
            }            
            getChange($('#total_sale').val());

            $('#btnCobro').addClass('d-none');
            $('input[name=status]').val('cobro');
        }

        //funcion para obtener el cambio
        function getChange(amount_received){
            // $('#btnAcept').addClass('d-none');
            let total = $('#total_sale').val();
            let change = amount_received - total;
            change = change.toFixed(2);
            
            $('#change').val(change);
            // if(change>=0 || parseInt(change) == 0){                
                $('#btnAcept').removeClass('d-none');
            // }
        }

        //funcion para buscar producto en modal
            //  document.addEventListener('DOMContentLoaded', function () {
            //     const input = document.getElementById("searchInput");
            //     input.addEventListener("keyup", function () {
            //     const filter = input.value.toLowerCase();
            //     const rows = document.querySelectorAll("#modal_products tbody tr");

            //     rows.forEach(row => {
            //         const text = row.textContent.toLowerCase();
            //         row.style.display = text.includes(filter) ? "" : "none";
            //     });
            //     });
        // });

        //funcion para validar que sea un valor numerico
        function esNumero(valor) {
            return /^\d*\.?\d+$/.test(valor);  // Permite .5, 123.45, etc.
        }

        //funcion para cerrar la venta
        function cerrarVenta(show = true) {
           $('#formSale').submit();
        }

        //funcion para cerrar venta
        function submitSale() {
            let amount_received = $('#amount_received').val();
            let total_sale = $('#total_sale').val();  

            let total_ajustado = ajustarMonto(total_sale);

            if (amount_received > 0 && amount_received >= total_ajustado) {
                Livewire.dispatch('cobrar', {
                    'monto': amount_received, 
                    'total_venta': total_sale,
                    'change': $('#change').val(),
                });
            } else {
                Swal.fire(
                    `La cantidad recibida es menor al total ajustado de la venta: ${total_ajustado.toFixed(2)}`,
                    '',
                    'info'
                );
            }
        }

        function ajustarMonto(monto) {
            let entero = Math.floor(monto);
            let decimal = monto - entero;

            if (decimal <= 0.25) {
                return entero;
            } else if (decimal <= 0.75) {
                return entero + 0.50;
            } else {
                return entero + 1;
            }
        }

        //funcion para mostrar ticket
        window.addEventListener('showTicket', event => {  
                $('#modalTicket iframe').attr('src', 'http://127.0.0.1:8100/ticket-sale/'+event.detail[0].sale_id+'/true');
                $('#modalTicket').show();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'F2') {
                e.preventDefault(); // evita el comportamiento por defecto de F2
                const modal = document.getElementById('modal_products');
                if (modal) {
                    modal.style.display = 'block'; // muestra el modal
                }
                $('#searchInput').focus().val('');
                $('#body_products').empty();
            }
        });

