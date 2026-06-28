<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = ['product_id', 'ingredient_name', 'quantity', 'unit'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
