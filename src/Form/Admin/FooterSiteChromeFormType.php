<?php

declare(strict_types=1);

namespace App\Form\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\TextEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Colonne pied (WYSIWYG) / réseaux sociaux. Marque bandeau + © suivent toujours le JSON stocké hors de ce formulaire.
 */
final class FooterSiteChromeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $baselinePayload = [];

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

        $builder->addEventListener(
            FormEvents::SUBMIT,
            static function (FormEvent $event) use (&$baselinePayload): void {
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
