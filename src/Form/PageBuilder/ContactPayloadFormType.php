<?php

declare(strict_types=1);

namespace App\Form\PageBuilder;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\TextEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Payload for Contact block — rich text sidebar + wired HTML form POSTing to ContactSubmitController.
 */
final class ContactPayloadFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de colonne gauche',
                'property_path' => '[titre]',
                'required' => false,
            ])
            ->add('introduction', TextareaType::class, [
                'label' => 'Accroche courte (texte simple, facultatif)',
                'property_path' => '[introduction]',
                'required' => false,
                'attr' => ['rows' => 4],
                'help' => 'Paragraphe d’introduction au-dessus du texte riche (pas d’éditeur WYSIWYG — pour éviter un double niveau HTML).',
            ])
            ->add('contenu', TextEditorType::class, [
                'label' => 'Texte principal (éditeur riche)',
                'property_path' => '[contenu]',
                'required' => false,
                'help' => 'Bloc HTML sous l’intro : services, tonalité, précisions sur vos délais ou process.',
            ]);

        $builder->add('titreEncart', TextType::class, [
            'label' => 'Petit bloc « Infos » — titre',
            'property_path' => '[titreEncart]',
            'required' => false,
            'help' => 'Souvent « Infos » lorsqu’un email ou téléphone est affiché.',
        ])
            ->add('emailAffiche', TextType::class, [
                'label' => 'Email affiché',
                'property_path' => '[emailAffiche]',
                'required' => false,
            ])
            ->add('telephoneAffiche', TextType::class, [
                'label' => 'Téléphone affiché',
                'property_path' => '[telephoneAffiche]',
                'required' => false,
            ])
            ->add('lienTelephone', TextType::class, [
                'label' => 'Numéro pour le lien tel:',
                'property_path' => '[lienTelephone]',
                'required' => false,
                'help' => 'Chiffres et + uniquement — ex. <code>+33612345678</code>',
            ]);

        $builder->add('mentionSoumission', TextareaType::class, [
            'label' => 'Mention sous le bouton Envoyer',
            'property_path' => '[mentionSoumission]',
            'required' => false,
            'attr' => ['rows' => 3],
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
