<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;
    protected  $fillable = [
        'name',
        'quantityBoite',
        'quantityPlaquette',
        'quantityGellule',
        'priceBoite',
        'pricePlaquette',
        'priceGellule',
        'numberPlaquette',
        'numberGellule',
        'datePeremption',
        'reference',
        'libele'
    ];

    public function  orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }

    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class);
    }

    public function enters(): BelongsToMany
    {
        return $this->belongsToMany(Enter::class);
    }
}
