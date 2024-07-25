<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logo extends Model
{
    use HasFactory;

    // Spécifiez les attributs qui peuvent être remplis en masse
    protected $fillable = ['path']; // Ajustez les attributs en fonction de votre besoin
}
