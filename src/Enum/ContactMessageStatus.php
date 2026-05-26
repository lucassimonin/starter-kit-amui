<?php

declare(strict_types=1);

namespace App\Enum;

enum ContactMessageStatus: string
{
    case New = 'new';
    case Read = 'read';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Nouveau',
            self::Read => 'Lu',
            self::Archived => 'Archivé',
        };
    }
}
