<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;
    public  $timestamps = false;
    protected  $fillable = ['dateCommande', 'user_id'];

    public function products(): BelongsToMany
    {
        return  $this->belongsToMany(Product::class)->withPivot(['quantityForOrder', 'fournisseurPrice', 'montantOrder']);
    }
}
