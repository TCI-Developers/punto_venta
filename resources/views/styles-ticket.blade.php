<style>
    /* sin esto, dompdf le mete su margen de pagina por defecto (1.2cm, pensado para carta/A4),
       que en un ticket de 80mm se come casi todo el ancho -- por eso antes se usaba un margen
       negativo enorme en el body para contrarrestarlo, lo cual empujaba el logo fuera del area
       visible. "margin: 0" exacto tiene un bug conocido en dompdf que rompe el render de
       imagenes; 1px evita ese bug y en la practica es visualmente igual a 0. */
    @page {
        margin: 1px;
    }
    body {
        font-family: 'DejaVu Sans Mono', monospace;
        font-size: 9px;
        margin: 0;
        padding: 0;
        background-color: white;
    }
    .ticket-container {
        padding: 2mm;
        margin: 0 auto;
        box-sizing: border-box;
    }
    .header, .footer {
        text-align: center;
        margin-bottom: 5px;
    }
    .info-venta {
        margin: 5px 0;
        border-top: 1px dashed #000;
        border-bottom: 1px dashed #000;
        padding: 3px 0;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    .table th {
        text-align: left;
        border-bottom: 1px dashed #000;
        padding: 2px 0;
    }
    .table td {
        padding: 3px 0;
    }
    .text-right {
        text-align: right;
    }
    .text-center {
        text-align: center;
    }
    .total {
        font-weight: bold;
        font-size: 14px;
    }
</style>