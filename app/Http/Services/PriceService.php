<?php

namespace App\Http\Services;

class PriceService
{
    public static function formatPrice($value) : string
    {
        return strrev(wordwrap(strrev($value), 3, ',', true)) . 'Ar';
    }

    public static function changePriceValidation($value)
    {
        return (int) preg_replace('/Ar|,/', '', $value);
    }
}
