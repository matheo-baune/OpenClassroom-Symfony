<?php

namespace App\enum;

enum BookStatus: string
{
    case Available = 'available';
    case Borrowed = 'borrowed';
    case Unavailable = 'unavailable';

    public function getLabel(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Borrowed => 'Emprunté',
            self::Unavailable => 'Indisponible',
        };
    }

    public static function getAllValue(): array
    {
        return [
            self::Available,
            self::Borrowed,
            self::Unavailable,
        ];
    }
}