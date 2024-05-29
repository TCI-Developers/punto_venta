$(".select-customer").select2({
    placeholder: "Buscar cliente",
    
    allowClear: true
});

$(".select-seller").select2({
    placeholder: "Buscar vendedor",
    theme: "classic",
    allowClear: true
});

const changeCustomer = (value) =>{
    $("#txtInformation").val(value);
}



// $(document).ready(function() {
//     $table = $('#tblOrder').DataTable( {
//         order: [[ 3, 'desc' ], [ 0, 'asc' ]],
//         language: {
//             "sProcessing":    "Procesando...",
//             "sLengthMenu":    "Mostrar _MENU_ registros",
//             "sZeroRecords":   "No se encontraron resultados",
//             "sEmptyTable":    "Ningún dato disponible en esta tabla",
//             "sInfo":          "Registros del _START_ al _END_ de _TOTAL_",
//             "sInfoEmpty":     "Mostrando registros del 0 al 0 de un total de 0 registros",
//             "sInfoFiltered":  "(filtrado de un total de _MAX_ registros)",
//             "sInfoPostFix":   "",
//             "sSearch":        "Buscar:",
//             "sUrl":           "",
//             "sInfoThousands":  ",",
//             "sLoadingRecords": "Cargando...",
//             "oPaginate": {
//                 "sFirst":    "Primero",
//                 "sLast":    "Último",
//                 "sNext":    "Siguiente",
//                 "sPrevious": "Anterior"
//             },
//             "oAria": {
//                 "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
//                 "sSortDescending": ": Activar para ordenar la columna de manera descendente"
//             }
//         }
//     } );

//     console.log($table);
// } );