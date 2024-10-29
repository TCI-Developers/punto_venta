    $(document).ready(function() {
        if(!$('#trEmpty').length){
            $('.datatable').DataTable({
                paging:false,
                searching: false,
                info: false,
            });
        }

        $('.selectpicker').selectpicker('refresh');
    })