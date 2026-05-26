<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * X (Twitter) card layout for shared links — https://developer.x.com/en/docs/twitter-for-websites/cards/overview/abouts-cards
 */
enum TwitterCardKind: string
{
    case Summary = 'summary';
    case SummaryLargeImage = 'summary_large_image';

    public function label(): string
    {
        return match ($this) {
            self::Summary => 'Résumé (image carré, compact)',
            self::SummaryLargeImage => 'Grande image (recommandé avec image OG)',
        };
    }
}
