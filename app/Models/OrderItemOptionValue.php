<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemOptionValue extends Model
{
    protected $table = 'order_item_option_value';

    protected $fillable = [
        'order_item_id',
        'option_value_id',
    ];
}
