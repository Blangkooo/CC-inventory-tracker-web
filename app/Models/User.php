<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, \Laravel\Sanctum\HasApiTokens;

    protected $fillable = ['name', 'pin_hash', 'role', 'branch_id'];
    protected $hidden = ['pin_hash'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
