<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'price'];

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }
}
