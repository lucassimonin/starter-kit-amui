<?php

declare(strict_types=1);

namespace App\Form\PageBuilder;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CardsGridPayloadFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titreSection', TextType::class, [
                'label' => 'Titre de section',
                'property_path' => '[titreSection]',
                'required' => false,
            ])
            ->add('descriptionSection', TextareaType::class, [
                'label' => 'Introduction / sous-texte',
                'property_path' => '[descriptionSection]',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('cartes', CollectionType::class, [
                'label' => 'Cartes',
                'property_path' => '[cartes]',
                'entry_type' => CarteItemPayloadFormType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'prototype' => true,
                'error_bubbling' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'data_class' => null,
            'compound' => true,
        ]);
    }
}
