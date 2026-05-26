<?php

declare(strict_types=1);

namespace App\Form\PageBuilder;

use App\Service\PublicFileUploader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Represents one image path row (string once submitted) for ImageGallery payloads.
 */
final class GalleryImageItemFormType extends AbstractType
{
    public function __construct(
        private readonly PublicFileUploader $uploader,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('path', TextType::class, [
                'label' => 'URL ou chemin public',
                'required' => false,
            ])
            ->add('file', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Téléversement',
                'attr' => ['accept' => 'image/*'],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event): void {
            $d = $event->getData();
            if (\is_array($d) && isset($d['path'])) {
                return;
            }
            if (\is_string($d) || null === $d) {
                $event->setData([
                    'path' => \is_string($d) ? $d : '',
                    'file' => null,
                ]);
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();
            if (!\is_array($data)) {
                return;
            }

            $form = $event->getForm();
            $upload = $form->get('file')->getData();
            $path = trim((string) ($data['path'] ?? ''));

            if ($upload instanceof UploadedFile && $upload->isValid()) {
                $path = $this->uploader->storeImage($upload);
            }

            $event->setData('' === $path ? null : $path);
        });
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
}
