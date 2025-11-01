<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptionValueProduct extends Model
{
    protected $table = 'option_value_product';

    protected $fillable = [
        'option_value_id',
        'product_id'
    ];
}
