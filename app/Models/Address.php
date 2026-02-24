<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';
    protected $fillable = [
        'user_id',
        'type',
        'full_name',
        'phone',
        'address_line',
        'area_id',
        'city_id',
        'is_default',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
