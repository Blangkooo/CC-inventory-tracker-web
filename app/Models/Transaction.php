<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['uuid', 'branch_id', 'user_id', 'product_id', 'quantity', 'synced'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
