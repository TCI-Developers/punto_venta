<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ManualController extends Controller
{
    public function index()
    {
        $modulos = [
            [
                'slug'        => 'ventas',
                'titulo'      => 'Ventas',
                'descripcion' => 'Cómo registrar una venta, cobrar, emitir ticket y facturar.',
                'icono'       => 'fa-cart-plus',
                'disponible'  => true,
            ],
            [
                'slug'        => 'facturas',
                'titulo'      => 'Facturas',
                'descripcion' => 'Cómo emitir y cancelar CFDIs 4.0.',
                'icono'       => 'fa-file-text-o',
                'disponible'  => false,
            ],
            [
                'slug'        => 'inventarios',
                'titulo'      => 'Inventarios',
                'descripcion' => 'Alta, edición y control de productos.',
                'icono'       => 'fa-folder',
                'disponible'  => false,
            ],
            [
                'slug'        => 'clientes',
                'titulo'      => 'Clientes',
                'descripcion' => 'Gestión del catálogo de clientes.',
                'icono'       => 'fa-users',
                'disponible'  => false,
            ],
            [
                'slug'        => 'cierre_caja',
                'titulo'      => 'Cierre de Turno',
                'descripcion' => 'Cómo realizar el corte de caja al final del turno.',
                'icono'       => 'fa-window-close',
                'disponible'  => false,
            ],
            [
                'slug'        => 'usuarios',
                'titulo'      => 'Usuarios y Roles',
                'descripcion' => 'Alta de usuarios y asignación de permisos.',
                'icono'       => 'fa-user',
                'disponible'  => false,
            ],
        ];

        return view('Admin.manual.index', compact('modulos'));
    }

    public function show(string $modulo)
    {
        $disponibles = ['ventas'];

        if (!in_array($modulo, $disponibles)) {
            return redirect()->route('manual.index')
                ->with('info', 'Ese módulo aún no tiene manual disponible.');
        }

        return view("Admin.manual.{$modulo}");
    }
}
