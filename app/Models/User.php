<?php

namespace App\Models;

//use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable //implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function markets(): BelongsToMany
    {
        return $this->belongsToMany(Market::class, 'user_market');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isMarketUser(): bool
    {
        return $this->role === UserRole::MARKET_USER;
    }

    public function canAccessMarket(Market $market): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->markets()->where('markets.id', $market->id)->exists();
    }

    public function getAccessibleMarketIds(): array
    {
        if ($this->isAdmin()) {
            return Market::active()->pluck('id')->toArray();
        }

        return $this->markets()->active()->pluck('markets.id')->toArray();
    }
}
