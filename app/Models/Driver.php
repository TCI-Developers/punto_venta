<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SaleDetail;

class Driver extends Model
{
    use HasFactory;
    protected $table = 'drivers';

    protected $fillable = [
        'id',
        'name',
    ];

    public function setDrivers($drivers) {
        if(count($drivers)){
            foreach($drivers as $item){
                $driver = Driver::find((int)$item->{'record_id#'});
                if(!isset($driver)){
                    $driver = new Driver();
                } 
                $driver->id = (int)$item->{'record_id#'};
                $driver->name = $item->nombre;
                $driver->save();
            }
            return true;
        }
        return false;
    }
}
