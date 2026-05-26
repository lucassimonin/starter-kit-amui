<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Page;
use App\Entity\SectionBlock;
use App\Entity\User;
use App\Enum\SectionLayout;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Seeds the demonstration one-page layout inspired by nuans/amui.html plus two demo admin users.
 * Intended for starter-kit previews (run: php bin/console doctrine:fixtures:load).
 */
final class StarterWebsiteFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Demo administrators (fixtures purge the DB first — local / staging only).
        $primaryAdmin = (new User())
            ->setEmail('admin@starter.kit')
            ->setRoles(['ROLE_ADMIN']);
        $primaryAdmin->setPassword($this->passwordHasher->hashPassword($primaryAdmin, 'admin-admin'));

        $secondaryAdmin = (new User())
            ->setEmail('studio@amui.demo')
            ->setRoles(['ROLE_ADMIN']);
        $secondaryAdmin->setPassword($this->passwordHasher->hashPassword($secondaryAdmin, 'studio-demo'));

        $home = new Page();
        $home->setTitle('amuï studio — démo Starter Kit')
            ->setSlug('amu-demo')
            ->setMetaTitle('amuï studio — Portfolio (démo)')
            ->setMetaDescription(
                'Créateurs d’expériences digitales — jeu de données de démonstration pour Starter Kit Symfony.',
            )
            ->setIsHomepage(true)
            ->setIsPublished(true)
            ->setFooterPayload([
                'brand' => ['name' => 'amuï', 'suffix' => 'studio'],
                'brandAria' => 'amuï studio — Retour en haut',
                'footerBrand' => 'amuï studio',
                'footerEmail' => 'hello@amui.studio',
                'footerPhone' => '+33 6 00 00 00 00',
                'footerPhoneHref' => '+33600000000',
                'socialTitle' => 'Réseaux',
                'socialLinks' => [
                    ['label' => 'LinkedIn', 'url' => '#', 'icon' => 'linkedin'],
                ],
                'copyrightName' => 'amuï studio',
                'copyrightTagline' => 'Minimalisme radical.',
            ]);

        // Hero (#top): structured like amui.html headline + accent line + CTAs + mark image.
        $hero = new SectionBlock();
        $hero->setLabel('Bloc hero démo')
            ->setAnchorId('top')
            ->setLayout(SectionLayout::Hero)
            ->setPosition(0)
            ->setIsEnabled(true)
            ->setPayload([
                'headline' => 'amuï studio :',
                'heroAccent' => 'Créateurs d’expériences digitales.',
                'subheadline' => 'Design minimal, systèmes clairs, interfaces qui vont droit au but. '
                    .'Le contraste est une signature — l’efficacité, une obsession.',
                'ctaLabel' => 'Démarrer un projet',
                'ctaUrl' => '#contact',
                'secondaryCtaLabel' => 'Voir les projets',
                'secondaryCtaUrl' => '#projets',
                'image' => 'https://images.unsplash.com/photo-1558655146-d09347e92766?auto=format&fit=crop&w=1200&q=80',
                'imageAlt' => 'Composition minimaliste noir et blanc',
            ]);
        $home->addSection($hero);

        $gallery = new SectionBlock();
        $gallery->setLabel('Bloc portfolio Projets')
            ->setAnchorId('projets')
            ->setLayout(SectionLayout::Gallery)
            ->setPosition(1)
            ->setIsEnabled(true)
            ->setPayload([
                'headline' => 'Projets',
                'navLabel' => 'Projets',
                'items' => [
                    [
                        'title' => 'L’Atelier Plomberie',
                        'subtitle' => 'Identité · Site · SEO',
                        'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=1200&q=80',
                        'imageAlt' => 'Aperçu du projet L\'Atelier Plomberie',
                        'url' => '#projets',
                        'aria' => 'Projet : L’Atelier Plomberie',
                        'linkLabel' => 'Voir',
                    ],
                    [
                        'title' => 'Nuans',
                        'subtitle' => 'E-commerce · UI System',
                        'image' => 'https://nuans-salon.com/wp-content/uploads/2018/12/Nuans-marchedulez-ambiance1-min.png',
                        'imageAlt' => 'Aperçu du projet Nuans — salon de coiffure',
                        'url' => '#projets',
                        'aria' => 'Projet : Nuans',
                        'linkLabel' => 'Voir',
                    ],
                    [
                        'title' => 'Référence Bois',
                        'subtitle' => 'Terrasse · Bardage · Vitrine',
                        'image' => 'https://www.referencebois.fr/wp-content/uploads/2024/12/terrasse-bois.jpg',
                        'imageAlt' => 'Aperçu du projet Référence Bois',
                        'url' => '#projets',
                        'aria' => 'Projet : Référence Bois',
                        'linkLabel' => 'Voir',
                    ],
                    [
                        'title' => 'Bivouak Café',
                        'subtitle' => 'Coffee shop · Lifestyle · One page',
                        'image' => 'https://www.bivouakcafe.fr/wp-content/uploads/2022/04/wim-lippens-BC02_HD-0031-1920x1091.jpg',
                        'imageAlt' => 'Aperçu du projet Bivouak Café',
                        'url' => '#projets',
                        'aria' => 'Projet : Bivouak Café',
                        'linkLabel' => 'Voir',
                    ],
                ],
            ]);
        $home->addSection($gallery);

        $about = new SectionBlock();
        $about->setLabel('Bloc Agence')
            ->setAnchorId('agence')
            ->setLayout(SectionLayout::About)
            ->setPosition(2)
            ->setIsEnabled(true)
            ->setPayload([
                'headline' => 'L’Agence',
                'navLabel' => 'Agence',
                'sidebarIntro' => 'Studio digital. Direction artistique, design système, front-end créatif.',
                'aboutLead' => 'Le minimalisme au service de l’efficacité.',
                'bodyHtml' => '<p>Nous construisons des expériences digitales structurées, rapides, '
                    .'et lisibles — du concept à l’interface. Chaque élément doit justifier sa présence. Rien de plus.</p>',
                'highlights' => [
                    ['title' => 'Design', 'text' => 'Identité · UI System · Typographie'],
                    ['title' => 'Développement', 'text' => 'Front-end · Performance · Accessibilité'],
                ],
            ]);
        $home->addSection($about);

        $contact = new SectionBlock();
        $contact->setLabel('Bloc contact démo')
            ->setAnchorId('contact')
            ->setLayout(SectionLayout::Contact)
            ->setPosition(3)
            ->setIsEnabled(true)
            ->setPayload([
                'headline' => 'Contact',
                'navLabel' => 'Contact',
                'subheadline' => 'Un message clair, une réponse rapide. Dites-nous ce que vous '
                    .'voulez faire — on vous dira comment le faire bien.',
                'infoBoxTitle' => 'Infos',
                'contactEmail' => 'hello@amui.studio',
                'contactPhone' => '+33 6 00 00 00 00',
                'contactPhoneHref' => '+33600000000',
                'formDisclaimer' => 'En envoyant, vous acceptez un retour par email.',
            ]);
        $home->addSection($contact);

        $manager->persist($primaryAdmin);
        $manager->persist($secondaryAdmin);
        $manager->persist($home);
        $manager->flush();
    }
}
