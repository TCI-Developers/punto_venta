<?php

return [
    /*
    |--------------------------------------------------------------------------
    | URL del microservicio de facturación
    |--------------------------------------------------------------------------
    | Ejemplo local:  http://localhost:9000
    | Ejemplo remoto: https://facturacion.miempresa.com
    */
    'url' => env('FACTURACION_URL', 'http://localhost:9000'),

    /*
    |--------------------------------------------------------------------------
    | Modo demo (sandbox Dinvbox)
    |--------------------------------------------------------------------------
    | true  → usa RFC EKU9003173C9 (pruebas, sin valor fiscal)
    | false → producción real
    |
    | Por defecto es true en cualquier ambiente que no sea production.
    */
    'demo' => env('FACTURACION_DEMO', true),
];
