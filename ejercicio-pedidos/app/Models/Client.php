<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Un Client puede tener un User asociado.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}