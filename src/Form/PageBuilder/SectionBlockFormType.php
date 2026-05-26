<?php

declare(strict_types=1);

namespace App\Form\PageBuilder;

use App\Entity\SectionBlock;
use App\Enum\SectionBlockKind;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SectionBlockFormType extends AbstractType
{
    public function __construct(
        private readonly SectionBlockPayloadMapper $payloadMapper,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Nom du bloc',
                'help' => 'Repère uniquement dans l’admin.',
            ])
            ->add('anchorId', TextType::class, [
                'label' => 'Ancre (sans #)',
                'required' => false,
                'help' => 'Ex. projet pour faire #projet depuis le menu.',
            ])
            ->add('showInNav', CheckboxType::class, [
                'label' => 'Afficher dans le menu du site',
                'required' => false,
                'help' =>
                    'Affiche un lien dans l’en-tête du site public vers cette ancre. '
                    .'Décoche pour garder une ancre (ancrage interne) sans entrée dans le menu.',
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'Visible sur le site public',
                'required' => false,
            ])
            ->add('kind', EnumType::class, [
                'class' => SectionBlockKind::class,
                'label' => 'Type de bloc générique',
                'choice_label' => static fn (SectionBlockKind $k): string => $k->label(),
            ])
            ->add('navLabel', TextType::class, [
                'mapped' => false,
                'label' => 'Libellé navigation (facultatif)',
                'required' => false,
                'help' => 'Libellé du lien dans le menu lorsque « Afficher dans le menu du site » est coché. '
                    .'Laissé vide : le nom du bloc est utilisé.',
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $this->onPreSet(...));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, $this->onPreSubmit(...));
        $builder->addEventListener(FormEvents::SUBMIT, $this->onSubmit(...), -2048);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SectionBlock::class,
        ]);
    }

    private function onPreSet(FormEvent $event): void
    {
        $block = $event->getData();
        $form = $event->getForm();

        if (!$block instanceof SectionBlock) {
            return;
        }

        $navPayload = '';

        /** @phpstan-ignore-next-line */
        if ($form->has('navLabel')) {
            /** @phpstan-ignore-next-line */
            $payload = $block->getPayload();
            if (\is_array($payload) && isset($payload['navLabel']) && \is_string($payload['navLabel'])) {
                $navPayload = trim($payload['navLabel']);
            }

            /** @phpstan-ignore-next-line */
            $form->get('navLabel')->setData($navPayload);
        }

        $this->resetPayloadBranch(
            $form,
            $block->getKind(),
            $this->payloadMapper->datasetForForm($block->getKind(), $block->getPayload()),
        );
    }

    private function onPreSubmit(FormEvent $event): void
    {
        $incoming = $event->getData();
        if (!\is_array($incoming)) {
            return;
        }

        $rawKind = $incoming['kind'] ?? SectionBlockKind::Hero->value;
        $kindValue = $rawKind instanceof SectionBlockKind ? $rawKind->value : (string) $rawKind;
        $kind = SectionBlockKind::tryFrom($kindValue) ?? SectionBlockKind::Hero;

        $postedSubset = [];

        /** @phpstan-ignore-next-line */
        if (\is_array($incoming['blockPayload'] ?? null)) {
            /** @phpstan-ignore-next-line */
            foreach ($incoming['blockPayload'] as $k => $value) {
                $postedSubset[(string) $k] = $value;
            }
        }

        $block = $event->getForm()->getData();

        /** @phpstan-ignore-next-line */
        $basePayload = [];

        /** @phpstan-ignore-next-line */
        if ($block instanceof SectionBlock && $block->getKind() === $kind) {
            /** @phpstan-ignore-next-line */
            $basePayload = $block->getPayload();

            foreach ($postedSubset as $k => $v) {
                $basePayload[(string) $k] = $v;
            }
        } elseif ([] !== $postedSubset) {
            /** @phpstan-ignore-next-line */
            $basePayload = $postedSubset;
        } elseif (
            /** @phpstan-ignore-next-line */
            $block instanceof SectionBlock
        ) {
            /** @phpstan-ignore-next-line */
            $basePayload = [];
        }

        /** @phpstan-ignore-next-line */
        $normalized = $this->payloadMapper->datasetForForm($kind, \is_array($basePayload) ? $basePayload : []);

        /** @phpstan-ignore-next-line */
        $this->resetPayloadBranch($event->getForm(), $kind, $normalized);
    }

    private function onSubmit(FormEvent $event): void
    {
        $block = $event->getData();
        if (!$block instanceof SectionBlock) {
            return;
        }

        $submittedNav = '';
        if ($event->getForm()->has('navLabel')) {
            /** @phpstan-ignore-next-line */
            $rawNav = $event->getForm()->get('navLabel')->getData();

            /** @phpstan-ignore-next-line */
            $submittedNav = \is_string($rawNav) ? trim($rawNav) : '';
        }

        $subset = [];

        if ($event->getForm()->has('blockPayload')) {
            /** @phpstan-ignore-next-line */
            $nested = $event->getForm()->get('blockPayload')->getData();

            /** @phpstan-ignore-next-line */
            $subset = \is_array($nested) ? $nested : [];
        }

        $priorPayload = $block->getPayload();
        if (\is_array($priorPayload)) {
            $priorNavLabel = $priorPayload['navLabel'] ?? null;
            // Nested payload forms omit navLabel; keep the stored value unless the top-level field overrides.
            if (!\array_key_exists('navLabel', $subset) && \is_string($priorNavLabel) && '' !== trim($priorNavLabel)) {
                $subset['navLabel'] = trim($priorNavLabel);
            }
        }

        $persistable = $this->payloadMapper->sanitizePersistedPayload($block->getKind(), $subset);

        if ('' !== $submittedNav) {
            $persistable['navLabel'] = $submittedNav;
        }

        $block->setPayload($persistable);
    }

    /** @param array<string, mixed> $payloadSubset */
    private function resetPayloadBranch(FormInterface $form, SectionBlockKind $kind, array $payloadSubset): void
    {
        if ($form->has('blockPayload')) {
            $form->remove('blockPayload');
        }

        $fqcn = match ($kind) {
            SectionBlockKind::Hero => HeroPayloadFormType::class,
            SectionBlockKind::TextImage => TextImagePayloadFormType::class,
            SectionBlockKind::CardsGrid => CardsGridPayloadFormType::class,
            SectionBlockKind::ImageGallery => ImageGalleryPayloadFormType::class,
            SectionBlockKind::Contact => ContactPayloadFormType::class,
        };

        $form->add('blockPayload', $fqcn, [
            'mapped' => false,
            'label' => false,
            'required' => false,
            'data' => $payloadSubset,

        ]);

    }

}
