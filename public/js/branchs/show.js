    let choicesInstance = null;

    document.addEventListener('DOMContentLoaded', function () {
        const userSelect = document.getElementById('user_id');
        if (userSelect) {
            choicesInstance = new Choices(userSelect, {
                removeItemButton: true,
                placeholder: true,
                placeholderValue: 'Selecciona uno o varios usuarios',
                searchEnabled: true,
                shouldSort: false,
                position: 'bottom',
            });

            // Desactivar si está en modo solo lectura
            if (userSelect.hasAttribute('disabled')) {
                choicesInstance.disable();
            }
        }
    });

    function edit() {            
        $('#btnEdit').addClass('d-none');
        $('#btnSubmit').removeClass('d-none');
        $('#btnCancel').removeClass('d-none');

        $('.inputs').attr('readonly', false);
        $('#user_id').prop('disabled', false);
        if (choicesInstance) choicesInstance.enable();
    }

    function cancel() {
        $('#btnEdit').removeClass('d-none');
        $('#btnSubmit').addClass('d-none');
        $('#btnCancel').addClass('d-none');
        $('.inputs').attr('readonly', true);
        $('#user_id').prop('disabled', true);
        if (choicesInstance) choicesInstance.disable();
    }

    function importAll(branch_id, value) {
        let route = '';
        if (value == 'productos') {
            route = "{{ route('import.products', ':id') }}".replace(':id', branch_id);
        } else if (value == 'choferes') {
            route = "{{ route('import.drivers', ':id') }}".replace(':id', branch_id);
        } else if (value == 'metodos_de_pago') {
            route = "{{ route('import.getPaymentMethods', ':id') }}".replace(':id', branch_id);
        } else if (value == 'proveedores') {
            route = "{{ route('proveedor.getProveedores', ':id') }}".replace(':id', branch_id);
        } else {
            route = "{{ route('import.getUnidadesSat', ':id') }}".replace(':id', branch_id);
        }

        Swal.fire({
            title: "¿Deseas importar " + value.replace('_', ' ') + " a esta sucursal?",
            showCancelButton: true,
            icon: 'question',
            confirmButtonText: "Aceptar",
            denyButtonText: `Cancelar`
        }).then((result) => {
            if (result.isConfirmed) {
                mostrarCargando();
                window.location.href = route;
            }
        });
    }

    function mostrarCargando() {
        Swal.fire({
            title: 'Cargando...',
            text: 'Por favor, espera mientras se completa el proceso.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }