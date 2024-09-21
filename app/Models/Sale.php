<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sale extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            $latestSale = Sale::latest('id')->first();
            $sale->invoice_number = $latestSale ? $latestSale->invoice_number + 1 : 1;
        });
    }


    protected $fillable = [
        'saleDate',
        'saleAmout',
        'salePayed',
        'amount_remaining',
        'saleStay',
        'estACredit',
        'playmentMode',
        'playmentDatePrevueAt',
        'clientName',
        'description',
        'stateSale',
        'remise',
        'invoice_number',
        'user_id',
    ];


    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_sale')
            ->withPivot(
                'quantityBoite', 
                'quantityGellule', 
                'quantityPlaquette', 
                'priceSaleBoite', 
                'priceSalePlaquette', 
                'priceSaleGellule', 
                'amount',
                'remise',
                'user'
            );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
