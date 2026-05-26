<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Supported generic reusable blocks managed from the Page form (starter kit defaults).
 */
enum SectionBlockKind: string
{
    case Hero = 'hero';
    case TextImage = 'text_image';
    case CardsGrid = 'cards_grid';
    case ImageGallery = 'image_gallery';
    case Contact = 'contact';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Hero — bannière (titre, image de fond, CTA)',
            self::TextImage => 'Texte + image — édition riche, ordre inversifiable',
            self::CardsGrid => 'Cartes — galerie d’éléments titre / texte / visuel',
            self::ImageGallery => 'Nuancier d’images — grille simple',
            self::Contact => 'Contact — texte riche + formulaire',
        };
    }

    public function template(): string
    {
        return match ($this) {
            self::Hero => 'sections/block_hero.html.twig',
            self::TextImage => 'sections/block_text_image.html.twig',
            self::CardsGrid => 'sections/block_cards_grid.html.twig',
            self::ImageGallery => 'sections/block_image_gallery.html.twig',
            self::Contact => 'sections/block_contact.html.twig',
        };
    }
}
