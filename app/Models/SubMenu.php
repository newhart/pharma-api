<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubMenu extends Model
{
    use HasFactory;
    protected $fillable = ['to', 'icon', 'text', 'menu_id'];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
