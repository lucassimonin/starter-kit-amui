<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SectionBlock;
use App\Enum\SectionLayout;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class SectionBlockCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SectionBlock::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Bloc de section')
            ->setEntityLabelInPlural('Blocs de sections')
            ->setPageTitle('index', 'Blocs de sections')
            ->setDefaultSort(['position' => 'ASC', 'id' => 'ASC'])
            ->showEntityActionsInlined()
            ->setHelp('index', 'Astuce : les champs « Contenu visuel » et « Boutons » alimentent automatiquement le JSON. '
                .'Laissez vides les champs non utilisés par votre type de section. '
                .'Pour l’ordre sur le site public, éditez « Ordre d’affichage » sur chaque bloc : '
                .'tri manuel ou tri des colonnes dans ce tableau **ne réordonne pas** les sections en front-office.');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::EDIT, Action::DETAIL, Action::DELETE]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(EntityFilter::new('page', 'Page'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex()->onlyOnDetail();

        yield FormField::addTab('Identification');

        yield AssociationField::new('page', 'Page')
            ->setRequired(true)
            ->autocomplete();

        yield TextField::new('label', 'Nom du bloc')
            ->setHelp('Nom interne pour vous repérer (non affiché au visiteur tel quel sur le front).');

        yield TextField::new('anchorId', 'Ancre (#...)')
            ->setHelp('Identifiant du fragment (ex. <code>projets</code> → lien <code>#projets</code>). Laisser vide si la section n’a pas besoin d’ancre.')
            ->hideOnIndex();

        yield ChoiceField::new('layout', 'Modèle visuel')->setChoices(
            array_combine(
                array_map(static fn (SectionLayout $l) => $l->label(), SectionLayout::cases()),
                SectionLayout::cases(),
            ),
        )->setHelp('Choisit quel gabarit Twig sera utilisé côté site (Étape 5 du starter).');

        yield IntegerField::new('position', 'Ordre d’affichage')
            ->setHelp('Plus le nombre est petit, plus le bloc est affiché haut dans la page.');

        yield BooleanField::new('isEnabled', 'Visible sur le site');

        yield FormField::addTab('Contenu visuel');

        yield TextField::new('payloadHeadline', 'Titre / accroche')
            ->hideOnIndex();

        yield TextareaField::new('payloadSubheadline', 'Sous-texte court')
            ->hideOnIndex();

        yield TextEditorField::new('payloadBodyHtml', 'Texte principal (éditeur riche)')
            ->hideOnIndex()
            ->setNumOfRows(10)
            ->setHelp('Mise en forme visuelle sans code : équivalent HTML stocké dans le champ JSON.');

        yield TextField::new('payloadImage', 'Image (URL ou chemin public)')
            ->hideOnIndex()
            ->setHelp('Exemples : URL complète HTTPS, ou chemin depuis <code>public/</code>.');

        yield FormField::addTab('Boutons & liens');

        yield TextField::new('payloadCtaLabel', 'Libellé du bouton principal')
            ->hideOnIndex();

        yield TextField::new('payloadCtaUrl', 'Lien du bouton principal')
            ->hideOnIndex();

        yield TextField::new('payloadSecondaryCtaLabel', 'Libellé du bouton secondaire')
            ->hideOnIndex();

        yield TextField::new('payloadSecondaryCtaUrl', 'Lien du bouton secondaire')
            ->hideOnIndex();

        yield FormField::addTab('Technique');

        yield ArrayField::new('payload', 'Données structurées (aperçu)')
            ->onlyOnDetail()
            ->hideOnForm();

        yield DateTimeField::new('createdAt', 'Créée le')->hideOnForm()->hideOnIndex();
        yield DateTimeField::new('updatedAt', 'Mise à jour')->hideOnForm()->hideOnIndex();
    }
}
