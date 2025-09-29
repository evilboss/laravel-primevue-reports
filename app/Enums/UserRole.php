<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case MARKET_USER = 'market_user';

    public function getDisplayName(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::MARKET_USER => 'Market User',
        };
    }
}