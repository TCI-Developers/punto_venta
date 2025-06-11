       var choicesInstances = {};

    document.addEventListener('DOMContentLoaded', function () {
        const elements = document.querySelectorAll('.choices');

            elements.forEach((el) => {
                const id = el.getAttribute('id');
                if (id) {
                    choicesInstances[id] = new Choices(el, {
                        removeItemButton: true,
                        shouldSort: false,
                        placeholder: true,
                        placeholderValue: 'Seleccione sus opciones...',
                        searchEnabled: true,
                    });
                }
            });

       // switch de la contraseña
        $('#switchPass').on('click', function(){
            if($(this).prop('checked')){
                $('#confirmedPass').attr('readonly', false);
                $('#password').attr('readonly', false);
                $('#btnUpdate').attr('disabled', true);
            }else{
                $('#confirmedPass').attr('readonly', true);
                $('#password').attr('readonly', true);
                $('#btnUpdate').attr('disabled', false);
            }
        });

        // Campo confirmar contraseña, para comparar si son iguales
        $('#confirmedPass').on('change', function(){
            let pass = $('#password').val();
            let confirmedPassword = $(this).val();

            if(pass != confirmedPassword){
                Swal.fire(
                    'Las contraseñas no coinciden.',
                    '',
                    'info'
                )
                $(this).val('');
                $('#password').val('');
            }else{
                $('#btnUpdate').attr('disabled', false);
            }
        });
    })

 
        
        //funcion para abrir modal crear
        function btnShow(user, user_branch){
            $('#modal_create').show();
            $('input[name=id]').val(user.id);

            // Obtener IDs como string
            let val = user.get_roles.map(item => item.role_id.toString());
            let branch = user_branch.map(item => item.branch_id.toString());

            // Limpiar y asignar nuevos valores a los selects con Choices
            if (choicesInstances['role_id']) {
                choicesInstances['role_id'].removeActiveItems();
                choicesInstances['role_id'].setChoiceByValue(val);
            }

            if (choicesInstances['branch_id']) {
                choicesInstances['branch_id'].removeActiveItems();
                choicesInstances['branch_id'].setChoiceByValue(branch);
            }

            $('#turno_id').val(user.turno_id);
        }

        //funcion para cerrar modal
        function btnCancel(){
            $('#modal_create').hide();
            $('.choices').val('');
        }

        //funcion para abrir modal edita usuario
        function modal(user){
            $('.inputs').val('');
            if(user !== 'null'){
                $('#formUser').attr('action', $('#formUser').attr('edit'));
                $('.title').html('Actualizar');
                $('input[name=user_id]').val(user.id);
                $('#name').val(user.name);
                $('#email').val(user.email);
                $('#phone').val(user.phone);
                $('#switch_pass').removeClass('d-none');
                $('.pass').attr('required', false).attr('readonly', true);
                $('#users').show();
            }else{
                $('#formUser').attr('action', $('#formUser').attr('store')); 
                $('#switch_pass').hide();
                $('#users').hide();
            }
        }

        function showModal() {                      
            $('#users').show();
        }