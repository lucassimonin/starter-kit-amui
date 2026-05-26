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

/**
 * One card inside CardsGrid (“cartes” collection items).
 */
final class CarteItemPayloadFormType extends AbstractType
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
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'property_path' => '[description]',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('imageOuIcone', TextType::class, [
                'label' => 'URL / chemin d’image (ou petite icône)',
                'property_path' => '[imageOuIcone]',
                'required' => false,
            ])
            ->add('imageOuIconeUpload', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Téléverser une image',
                'attr' => ['accept' => 'image/*'],
            ])
            ->add('lien', TextType::class, [
                'label' => 'Lien (interne avec # ou URL)',
                'property_path' => '[lien]',
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
        $upload = $form->get('imageOuIconeUpload')->getData();
        if (!$upload instanceof UploadedFile || !$upload->isValid()) {
            return;
        }

        $data = $event->getData();
        \assert(\is_array($data));
        $data['imageOuIcone'] = $this->uploader->storeImage($upload);
        $event->setData($data);
    }
}
