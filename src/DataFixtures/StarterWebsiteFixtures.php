<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Page;
use App\Entity\SectionBlock;
use App\Entity\User;
use App\Enum\SectionBlockKind;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Seeds a demo one-page (hero + cartes + texte/image + contact).
 */
final class StarterWebsiteFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
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
                'brand' => [
                    'line' => 'amuï studio',
                ],
                'footerContentHtml' =>
                    '<p class="font-display text-lg font-black tracking-tight text-ink">amuï studio</p>'
                    .'<p class="mt-3 text-sm text-ink/70">'
                    .'<a class="underline-offset-2 hover:underline" href="mailto:hello@amui.studio">hello@amui.studio</a>'
                    .'<br>'
                    .'<a class="underline-offset-2 hover:underline" href="tel:+33600000000">+33 6 00 00 00 00</a>'
                    .'</p>',
                'socialTitle' => 'Réseaux',
                'socialLinks' => [
                    ['label' => 'LinkedIn', 'url' => 'https://linkedin.com'],
                ],
                'copyrightName' => 'amuï studio',
                'copyrightTagline' => 'Minimalisme radical.',
                'faviconHref' => '/images/amui-studio-logo.png',
                'appleTouchIcon' => '/images/amui-studio-logo.png',
                'themeColor' => '#000000',
            ]);

        $hero = new SectionBlock();
        $hero->setLabel('Bloc Hero')
            ->setAnchorId('top')
            ->setKind(SectionBlockKind::Hero)
            ->setPosition(0)
            ->setIsEnabled(true)
            ->setPayload([
                'titre' => 'amuï studio : créateurs numériques.',
                'sousTitre' => 'Design minimal, systèmes clairs, interfaces qui vont droit au but. '
                    .'Contraste franc, lignes précises.',
                'imageFond' => '/images/amui-studio-logo.png',
                'ctaTexte' => 'Voir les références',
                'ctaLien' => '#contact',
                'navLabel' => 'Accueil',
            ]);
        $home->addSection($hero);

        $portfolio = new SectionBlock();
        $portfolio->setLabel('Cartes projet')
            ->setAnchorId('projets')
            ->setKind(SectionBlockKind::CardsGrid)
            ->setPosition(1)
            ->setIsEnabled(true)
            ->setPayload([
                'titreSection' => 'Projets',
                'descriptionSection' => 'Quelques terrains où le minimalisme noir & blanc gagne contre le bruit visuel.',
                'navLabel' => 'Projets',
                'cartes' => [
                    [
                        'titre' => 'L’Atelier Plomberie',
                        'description' => 'Identité · Site vitrine · SEO local',
                        'imageOuIcone' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=1200&q=80',
                        'lien' => '#projets',
                    ],
                    [
                        'titre' => 'Nuans',
                        'description' => 'Retail beauté · E-commerce léger · UI très épurée',
                        'imageOuIcone' => 'https://nuans-salon.com/wp-content/uploads/2018/12/Nuans-marchedulez-ambiance1-min.png',
                        'lien' => '#projets',
                    ],
                    [
                        'titre' => 'Référence Bois',
                        'description' => 'Terrasse · Photo produits · Showcase simple',
                        'imageOuIcone' => 'https://www.referencebois.fr/wp-content/uploads/2024/12/terrasse-bois.jpg',
                        'lien' => '#projets',
                    ],
                    [
                        'titre' => 'Bivouak Café',
                        'description' => 'Coffee shop · Lifestyle · One page pleine vue',
                        'imageOuIcone' => 'https://www.bivouakcafe.fr/wp-content/uploads/2022/04/wim-lippens-BC02_HD-0031-1920x1091.jpg',
                        'lien' => '#projets',
                    ],
                ],
            ]);
        $home->addSection($portfolio);

        $agence = new SectionBlock();
        $agence->setLabel('Présentation agence')
            ->setAnchorId('agence')
            ->setKind(SectionBlockKind::TextImage)
            ->setPosition(2)
            ->setIsEnabled(true)
            ->setPayload([
                'titre' => 'L’Agence',
                'contenu' => '<p>Nous façonnons des expériences web rapides et lisibles&nbsp;: direction artistique, '
                    .'interfaces soignées, front-end précis du concept à la livraison.</p>'
                    .'<p>Un site one-page doit raconter vite, sans parasite. Nos blocs génériques servent précisément '
                    .'cette narration modulaire.</p>',
                'image' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=900&q=80',
                'inverserOrdre' => false,
                'navLabel' => 'Agence',
            ]);
        $home->addSection($agence);

        $contact = new SectionBlock();
        $contact->setLabel('Bloc contact')
            ->setAnchorId('contact')
            ->setKind(SectionBlockKind::Contact)
            ->setPosition(3)
            ->setIsEnabled(true)
            ->setPayload([
                'titre' => 'Contact',
                'introduction' => 'Un message clair, une réponse rapide. Dites-nous ce que vous '
                    .'voulez faire — on vous dira comment le faire bien.',
                'contenu' => '<p>Pour un démarrage efficace, indiquez votre secteur, une contrainte '
                    .'(planning, budget indicatif) et le canal de retour privilégié.</p>',
                'titreEncart' => 'Infos',
                'emailAffiche' => 'hello@amui.studio',
                'telephoneAffiche' => '+33 6 00 00 00 00',
                'lienTelephone' => '+33600000000',
                'mentionSoumission' => 'En envoyant, vous acceptez un retour par email.',
                'navLabel' => 'Contact',
            ]);
        $home->addSection($contact);

        $manager->persist($primaryAdmin);
        $manager->persist($secondaryAdmin);
        $manager->persist($home);
        $manager->flush();
    }
}
