<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sale extends Model
{
    use HasFactory;
    protected $fillable = [
        'saleDate',
        'saleAmout',
        'salePayed',
        'saleStay',
        'estACredit',
        'playmentMode',
        'playmentDatePrevueAt',
        'clientName',
        'description',
        'stateSale',
        'remise', // Ajouté ici
    ];


    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
