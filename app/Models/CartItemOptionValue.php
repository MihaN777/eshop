<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItemOptionValue extends Model
{
    protected $table = 'cart_item_option_value';

    protected $fillable = [
        'cart_item_id',
        'option_value_id'
    ];
}
