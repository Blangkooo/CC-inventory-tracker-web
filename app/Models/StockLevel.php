<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLevel extends Model
{
    public $timestamps = false;
    protected $fillable = ['branch_id', 'ingredient_name', 'quantity', 'unit', 'updated_at'];
}
