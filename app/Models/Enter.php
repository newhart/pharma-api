<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Enter extends Model
{
    use HasFactory;
    public  $timestamps = false ;
    protected  $fillable = ['dateEntrer'];

    public function products() : BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

}
