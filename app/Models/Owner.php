<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Owner extends Authenticatable implements FilamentUser

{
    use HasFactory;

    protected $fillable = [
        'name',
        'username',
        'password',
    ];
    public function businesses()
    {
        return $this->hasMany(Business::class);
    }
    protected $casts = [
        'password' => 'hashed',
    ];
    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'owner';
    }
}
