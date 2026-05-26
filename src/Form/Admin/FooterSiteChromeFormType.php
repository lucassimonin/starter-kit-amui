<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Service\PublicFileUploader;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\TextEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Colonne pied (WYSIWYG) / réseaux sociaux / favicon / bandeau cookies. Marque bandeau + © peuvent être maintenus hors formulaire via le JSON en fiche détail.
 */
final class FooterSiteChromeFormType extends AbstractType
{
    public function __construct(
        private readonly PublicFileUploader $uploader,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $baselinePayload = [];
        $uploader = $this->uploader;

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            static function (FormEvent $event) use (&$baselinePayload): void {
                $data = $event->getData();
                if (!\is_array($data)) {
                    $baselinePayload = [];

                    return;
                }

                self::mergeLegacyBrandNameSuffixIntoLine($data);
                $event->setData($data);

                // Detached copy so later form mutations do not alter the merge source.
                $baselinePayload = json_decode(json_encode($data, \JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR);
            },
        );

        $builder
            ->add('footerContentHtml', TextEditorType::class, [
                'label' => 'Bloc gauche du pied de page',
                'property_path' => '[footerContentHtml]',
                'required' => false,
                'help' => 'Texte riche pour la colonne de gauche. Marque du bandeau et ligne © suivent encore le JSON enregistré (fiche détail).',
            ]);

        $builder
            ->add('socialTitle', TextType::class, [
                'label' => 'Titre du bloc réseaux',
                'property_path' => '[socialTitle]',
                'required' => false,
            ])
            ->add('socialLinks', CollectionType::class, [
                'label' => 'Liens réseaux & externes',
                'property_path' => '[socialLinks]',
                'entry_type' => SocialLinkItemFormType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => static function (?array $row): bool {
                    if (null === $row || [] === $row) {
                        return true;
                    }
                    $l = isset($row['label']) && \is_string($row['label']) ? trim($row['label']) : '';
                    $u = isset($row['url']) && \is_string($row['url']) ? trim($row['url']) : '';

                    return '' === $l && '' === $u;
                },
                'by_reference' => false,
                'prototype' => true,
                'error_bubbling' => false,
            ]);

        $builder
            ->add('faviconHref', TextType::class, [
                'label' => 'Favicon',
                'property_path' => '[faviconHref]',
                'required' => false,
                'help' => 'URL HTTPS ou chemin public (ex. <code>/images/favicon.ico</code>), ou téléversez un fichier ci-dessous.',
            ])
            ->add('faviconUpload', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Téléverser un favicon',
                'help' => 'PNG, ICO ou SVG ; enregistré sous <code>/uploads/page-builder/</code> et défini automatiquement comme favicon.',
                'attr' => [
                    'accept' => '.ico,.png,.svg,image/png,image/x-icon,image/vnd.microsoft.icon,image/svg+xml',
                ],
            ])
            ->add('appleTouchIcon', TextType::class, [
                'label' => 'Icône Apple Touch',
                'property_path' => '[appleTouchIcon]',
                'required' => false,
                'help' => 'Recommandé environ 180×180 — &lt;link rel="apple-touch-icon"&gt;.',
            ])
            ->add('themeColor', ColorType::class, [
                'label' => 'Couleur thème (barre navigateur mobile)',
                'property_path' => '[themeColor]',
                'required' => false,
                'help' => 'Méta <code>theme-color</code> pour les navigateurs compatibles.',
            ])
            ->add('cookieBannerEnabled', CheckboxType::class, [
                'label' => 'Afficher le bandeau cookies (bas de page)',
                'property_path' => '[cookieBannerEnabled]',
                'required' => false,
            ])
            ->add('cookieBannerMessage', TextareaType::class, [
                'label' => 'Message du bandeau cookies',
                'property_path' => '[cookieBannerMessage]',
                'required' => false,
                'attr' => ['rows' => 4],
                'help' => 'Texte court et clair pour les visiteurs. Laissé vide : message par défaut côté site.',
            ])
            ->add('cookieAcceptLabel', TextType::class, [
                'label' => 'Libellé bouton « accepter »',
                'property_path' => '[cookieAcceptLabel]',
                'required' => false,
                'help' => 'Vide = « J’accepte ».',
            ])
            ->add('cookieMoreLabel', TextType::class, [
                'label' => 'Libellé lien (politique / mentions)',
                'property_path' => '[cookieMoreLabel]',
                'required' => false,
                'help' => 'Affiché seulement si une URL est renseignée ci‑dessous. Vide = « En savoir plus ».',
            ])
            ->add('cookiePolicyUrl', TextType::class, [
                'label' => 'URL de la page d’informations (optionnel)',
                'property_path' => '[cookiePolicyUrl]',
                'required' => false,
                'help' => 'Chemin relatif ou URL HTTPS (ex. <code>/mentions-legales</code>).',
            ]);

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use (&$baselinePayload, $uploader): void {
                $incoming = $event->getData();
                if (!\is_array($incoming)) {
                    $event->setData(null);

                    return;
                }

                $merged = $baselinePayload;

                $html = $incoming['footerContentHtml'] ?? null;
                if (\is_string($html) && '' !== trim($html)) {
                    $merged['footerContentHtml'] = $html;
                } else {
                    unset($merged['footerContentHtml']);
                }

                $socialTitle = $incoming['socialTitle'] ?? null;
                if (\is_string($socialTitle) && '' !== trim($socialTitle)) {
                    $merged['socialTitle'] = trim($socialTitle);
                } else {
                    unset($merged['socialTitle']);
                }

                if (isset($incoming['socialLinks']) && \is_array($incoming['socialLinks'])) {
                    $links = self::normalizeSocialLinkRows($incoming['socialLinks']);
                    if ([] !== $links) {
                        $merged['socialLinks'] = $links;
                    } else {
                        unset($merged['socialLinks']);
                    }
                }

                $faviconUploaded = false;
                $upload = $incoming['faviconUpload'] ?? null;
                if ($upload instanceof UploadedFile && $upload->isValid()) {
                    $merged['faviconHref'] = $uploader->storeImage($upload);
                    $faviconUploaded = true;
                }

                if (!$faviconUploaded) {
                    $faviconHref = $incoming['faviconHref'] ?? null;
                    if (\is_string($faviconHref) && '' !== trim($faviconHref)) {
                        $merged['faviconHref'] = trim($faviconHref);
                    } else {
                        unset($merged['faviconHref']);
                    }
                }

                $appleTouch = $incoming['appleTouchIcon'] ?? null;
                if (\is_string($appleTouch) && '' !== trim($appleTouch)) {
                    $merged['appleTouchIcon'] = trim($appleTouch);
                } else {
                    unset($merged['appleTouchIcon']);
                }

                $themeColor = $incoming['themeColor'] ?? null;
                if (\is_string($themeColor) && '' !== trim($themeColor)) {
                    $merged['themeColor'] = trim($themeColor);
                } else {
                    unset($merged['themeColor']);
                }

                unset(
                    $merged['cookieBannerEnabled'],
                    $merged['cookieBannerMessage'],
                    $merged['cookieAcceptLabel'],
                    $merged['cookieMoreLabel'],
                    $merged['cookiePolicyUrl'],
                );

                $cookieEnabled = false;
                if (isset($incoming['cookieBannerEnabled'])) {
                    $raw = $incoming['cookieBannerEnabled'];
                    $cookieEnabled = true === $raw || 1 === $raw || '1' === $raw || 'on' === $raw;
                }

                if ($cookieEnabled) {
                    $merged['cookieBannerEnabled'] = true;

                    $cookieMsg = $incoming['cookieBannerMessage'] ?? null;
                    if (\is_string($cookieMsg) && '' !== trim($cookieMsg)) {
                        $merged['cookieBannerMessage'] = trim($cookieMsg);
                    }

                    foreach (
                        [
                            'cookieAcceptLabel' => 'cookieAcceptLabel',
                            'cookieMoreLabel' => 'cookieMoreLabel',
                            'cookiePolicyUrl' => 'cookiePolicyUrl',
                        ] as $incomingKey => $storageKey
                    ) {
                        $val = $incoming[$incomingKey] ?? null;
                        if (\is_string($val) && '' !== trim($val)) {
                            $merged[$storageKey] = trim($val);
                        }
                    }
                }

                $event->setData([] === $merged ? null : $merged);
            },
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'data_class' => null,
            'compound' => true,
            'allow_file_upload' => false,
        ]);

        /*
         * EasyAdmin remplace Field::{footerPayload} par ArrayField (type JSON Doctrine), qui prépare des
         * options CollectionType héritées — acceptées ici sans effet.
         */
        $resolver->setDefined([
            'allow_add',
            'allow_delete',
            'delete_empty',
            'entry_type',
            'entry_options',
            'prototype',
            'prototype_name',
            'prototype_data',
            'by_reference',
            'preserve_keys',
            'allow_file_upload',
        ]);
    }

    /**
     * @param list<mixed> $rows
     *
     * @return list<array<string, string>>
     */
    private static function normalizeSocialLinkRows(array $rows): array
    {
        $links = [];

        foreach ($rows as $row) {
            if (!\is_array($row)) {
                continue;
            }

            $link = [];

            foreach (['label', 'url'] as $fk) {
                if (!isset($row[$fk]) || !\is_string($row[$fk])) {
                    continue;
                }

                $fv = trim($row[$fk]);
                if ('' !== $fv) {
                    $link[$fk] = $fv;
                }
            }

            if ([] === $link) {
                continue;
            }

            if (!isset($link['label']) && !isset($link['url'])) {
                continue;
            }

            $links[] = $link;
        }

        return array_values($links);
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function mergeLegacyBrandNameSuffixIntoLine(array &$data): void
    {
        $brand = $data['brand'] ?? null;
        if (!\is_array($brand)) {
            return;
        }

        $line = isset($brand['line']) && \is_string($brand['line']) ? trim($brand['line']) : '';
        if ('' !== $line) {
            return;
        }

        $name = isset($brand['name']) && \is_string($brand['name']) ? trim($brand['name']) : '';
        $suffix = isset($brand['suffix']) && \is_string($brand['suffix']) ? trim($brand['suffix']) : '';
        if ('' === $name && '' === $suffix) {
            return;
        }

        $merged = trim($name.('' !== $name && '' !== $suffix ? ' ' : '').$suffix);
        if ('' !== $merged) {
            $data['brand']['line'] = $merged;
        }
    }
}
