<?php

declare(strict_types=1);

namespace App\Form\PageBuilder;

use App\Service\PublicFileUploader;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\TextEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TextImagePayloadFormType extends AbstractType
{
    public function __construct(
        private readonly PublicFileUploader $uploader,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'property_path' => '[titre]',
                'required' => false,
            ])
            ->add('contenu', TextEditorType::class, [
                'label' => 'Contenu',
                'property_path' => '[contenu]',
                'required' => false,
            ])
            ->add('image', TextType::class, [
                'label' => 'Image (URL ou chemin)',
                'property_path' => '[image]',
                'required' => false,
            ])
            ->add('imageUpload', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Ou téléversez une image',
                'attr' => ['accept' => 'image/*'],
            ])
            ->add('inverserOrdre', CheckboxType::class, [
                'label' => 'Placer le visuel à gauche (inverse le sens desktop)',
                'property_path' => '[inverserOrdre]',
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
        $data = $event->getData();
        \assert(\is_array($data));

        $data['inverserOrdre'] = (bool) ($form->get('inverserOrdre')->getData() ?? false);

        $upload = $form->get('imageUpload')->getData();
        if (!$upload instanceof UploadedFile || !$upload->isValid()) {
            $event->setData($data);

            return;
        }

        $data['image'] = $this->uploader->storeImage($upload);
        $event->setData($data);
    }
}
