    $(document).ready(function() {
        if(!$('#trEmpty').length){
            $('.datatable').DataTable({
                paging:false,
                searching: false,
                info: false,
                ordering: false,
            });
        }

        $('.selectpicker').selectpicker('refresh');
    })