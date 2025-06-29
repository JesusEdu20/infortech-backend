<?php

namespace App\Models;

use App\Models\Model;

class Quotations extends Model
{
    protected static string $table = 'quotations';
    public static $fillable = [
        'description',
        'subTotal',
        'iva',
        'igtf',
        'total',
        'status',
        'created_at',
        'updated_at'
    ];
};
