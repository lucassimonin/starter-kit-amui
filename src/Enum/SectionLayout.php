<?php

declare(strict_types=1);

namespace App\Enum;

enum SectionLayout: string
{
    case Hero = 'hero';
    case About = 'about';
    case Services = 'services';
    case Features = 'features';
    case Gallery = 'gallery';
    case Testimonials = 'testimonials';
    case Pricing = 'pricing';
    case Faq = 'faq';
    case Cta = 'cta';
    case Contact = 'contact';
    case RichText = 'rich_text';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Section "Hero" (bannière haute)',
            self::About => 'À propos',
            self::Services => 'Services',
            self::Features => 'Caractéristiques / Atouts',
            self::Gallery => 'Galerie d\'images',
            self::Testimonials => 'Témoignages',
            self::Pricing => 'Tarifs',
            self::Faq => 'FAQ',
            self::Cta => 'Appel à l\'action (CTA)',
            self::Contact => 'Formulaire de contact',
            self::RichText => 'Bloc de texte libre',
        };
    }

    public function template(): string
    {
        return 'sections/'.$this->value.'.html.twig';
    }
}
