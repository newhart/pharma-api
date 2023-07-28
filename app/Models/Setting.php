<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;
    protected  $fillable = ['limit', 'type', 'user_id', 'color'];

    public function user(): BelongsTo
    {
        return  $this->belongsTo(User::class);
    }
}
