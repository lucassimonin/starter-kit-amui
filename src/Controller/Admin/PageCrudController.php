<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Page;
use App\Form\Admin\FooterSiteChromeFormType;
use App\Form\PageBuilder\SectionBlockFormType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class PageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Page')
            ->setEntityLabelInPlural('Pages')
            ->setPageTitle('index', 'Pages & SEO')
            ->setDefaultSort(['title' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_EDIT, Action::DETAIL);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('slug')
            ->add('isPublished')
            ->add('isHomepage');
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Page) {
            $this->normalizeSectionOrdering($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Page) {
            $this->normalizeSectionOrdering($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureFields(string $pageName): iterable
    {
        // With form tabs (NEW/EDIT/DETAIL), every field must sit under a tab: start with a tab, not with IdField.
        yield FormField::addTab('Informations générales', 'fa-solid fa-id-card');
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre interne');
        yield TextField::new('slug', 'Slug (URL)')
            ->setHelp('Minuscules, chiffres et tirets uniquement — ex. <code>mentions-legales</code>');
        yield BooleanField::new('isHomepage', 'Page d\'accueil');
        yield BooleanField::new('isPublished', 'Publiée');
        yield DateTimeField::new('createdAt', 'Créée le')
            ->hideOnForm()
            ->hideOnIndex();
        yield DateTimeField::new('updatedAt', 'Mise à jour')
            ->hideOnForm()
            ->hideOnIndex();

        yield FormField::addTab('SEO', 'fa-solid fa-magnifying-glass')
            ->setHelp('Titres pour les résultats Google et le partage social.');
        yield TextField::new('metaTitle', 'Meta title')
            ->setHelp('Titre affiché dans Google (environ 60 caractères).');
        yield TextField::new('metaDescription', 'Meta description')
            ->setHelp('Texte court pour les résultats Google (environ 155 caractères).');
        yield TextField::new('ogImage', 'Image Open Graph (URL ou chemin)')
            ->hideOnIndex();

        yield FormField::addTab('Bandeau & pied', 'fa-solid fa-window-maximize');
        yield Field::new('footerPayload', 'Pied de page')
            ->setFormType(FooterSiteChromeFormType::class)
            ->hideOnIndex()
            ->onlyOnForms()
            ->setHelp(
                'Bloc gauche du footer en éditeur riche ; titre et liens réseaux en dessous. '
                .'Marque du bandeau et ligne © : toujours visibles/modifiables via le JSON en fiche détail.'
            );
        yield ArrayField::new('footerPayload', 'Chrome site (JSON — lecture)')
            ->onlyOnDetail()
            ->setHelp('Vue compacte du JSON « Bandeau & pied » (mêmes clés qu’à l’édition).');

        yield FormField::addTab('Blocs (page builder)', 'fa-solid fa-bars');
        yield CollectionField::new('sections', 'Sections')
            ->onlyOnForms()
            ->setEntryIsComplex(true)
            ->setEntryType(SectionBlockFormType::class)
            ->setFormTypeOption('allow_file_upload', true)
            ->setFormTypeOption('by_reference', false)
            ->setHelp(
                'Choisissez un type générique puis remplissez le payload dynamique '
                .'(titres texte, téléversements, cartes ou galerie). '
                .'L’ordre d’insertion définit l’affichage public (du premier au dernier de la liste).',
            )
            ->renderExpanded();
    }

    private function normalizeSectionOrdering(Page $page): void
    {
        foreach (array_values($page->getSections()->toArray()) as $idx => $section) {
            $section->setPosition($idx);
            $section->setPage($page);
        }
    }
}
