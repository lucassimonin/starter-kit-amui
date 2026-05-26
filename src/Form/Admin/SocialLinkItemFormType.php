<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * One social link row inside footerPayload.socialLinks (visible text + href only).
 */
final class SocialLinkItemFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Texte',
                'property_path' => '[label]',
                'required' => false,
            ])
            ->add('url', TextType::class, [
                'label' => 'Lien',
                'property_path' => '[url]',
                'required' => false,
                'help' => 'URL complète ou ancre (#…). Pour ouvrir dans un nouvel onglet, préférez une URL HTTPS.',
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
