<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';
    protected $fillable = [
        'city_id',
        'name',
        'delivery_charge',
        'status',
    ];
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
