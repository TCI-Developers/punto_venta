<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;
    protected $table = 'facturas';

    protected $fillable = [
        'uuid', 'folio_fiscal', 'serie', 'folio',
        'tipo_comprobante', 'customer_id', 'branch_id', 'user_id',
        'subtotal', 'descuento', 'iva', 'total',
        'forma_pago', 'metodo_pago', 'uso_cfdi', 'moneda',
        'status', 'is_demo', 'relacionado_uuid', 'foliosust', 'xml', 'pdf_url', 'error_message', 'response_json',
    ];

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer', 'customer_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function sales()
    {
        return $this->belongsToMany('App\Models\Sale', 'factura_sales', 'factura_id', 'sale_id');
    }

    public function getStatusLabel(): string
    {
        return match((int) $this->status) {
            0 => 'Pendiente',
            1 => 'Timbrada',
            2 => 'Cancelada',
            3 => 'Error',
            default => 'Desconocido',
        };
    }

    public function getStatusBadge(): string
    {
        return match((int) $this->status) {
            0 => 'secondary',
            1 => 'success',
            2 => 'danger',
            3 => 'warning',
            default => 'dark',
        };
    }
}
