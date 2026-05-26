<?php

declare(strict_types=1);

namespace App\Form\PageBuilder;

use App\Service\PublicFileUploader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HeroPayloadFormType extends AbstractType
{
    public function __construct(
        private readonly PublicFileUploader $uploader,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre principal',
                'property_path' => '[titre]',
                'required' => false,
            ])
            ->add('sousTitre', TextareaType::class, [
                'label' => 'Sous-titre',
                'property_path' => '[sousTitre]',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('imageFond', TextType::class, [
                'label' => 'Image de fond (URL ou chemin public)',
                'property_path' => '[imageFond]',
                'required' => false,
                'help' => 'Saisissez une URL HTTPS ou un chemin commençant par /uploads/… — ou téléversez ci-dessous.',
            ])
            ->add('imageFondUpload', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Ou téléversez une image',
                'attr' => ['accept' => 'image/*'],
            ])
            ->add('ctaTexte', TextType::class, [
                'label' => 'Texte du bouton (CTA)',
                'property_path' => '[ctaTexte]',
                'required' => false,
            ])
            ->add('ctaLien', TextType::class, [
                'label' => 'Lien du bouton',
                'property_path' => '[ctaLien]',
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, $this->applyUpload(...));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'data_class' => null,
            'compound' => true,
            'allow_file_upload' => true,
        ]);
    }

    private function applyUpload(FormEvent $event): void
    {
        $form = $event->getForm();
        $upload = $form->get('imageFondUpload')->getData();
        if (!$upload instanceof UploadedFile || !$upload->isValid()) {
            return;
        }

        $data = $event->getData();
        \assert(\is_array($data));
        $data['imageFond'] = $this->uploader->storeImage($upload);
        $event->setData($data);
    }
}
