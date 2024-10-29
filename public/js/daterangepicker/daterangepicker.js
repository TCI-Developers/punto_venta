$(document).ready(function() {
    // var start = moment().subtract(29, 'days');
    var start = moment();
    var end = moment();
    var position;
    $.each($('.reportrange'), function(index, event){
        position = $(event).attr('position');
        $(event).daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
            'Hoy': [moment(), moment()],
            'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Ultimos 7 dias': [moment().subtract(6, 'days'), moment()],
            'Utimos 30 dias': [moment().subtract(29, 'days'), moment()],
            'Este mes': [moment().startOf('month'), moment().endOf('month')],
            'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });
        $('#reportrange-'+position+' span').html(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
    });
});