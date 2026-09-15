<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone', // DIGANTI DARI EMAIL
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // TAMBAHKAN INI: Supaya Laravel Auth pakai kolom 'phone' untuk login
    public function username()
    {
        return 'phone';
    }
}