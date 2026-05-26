<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Page;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PageCrudController extends AbstractCrudController
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

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex()->onlyOnDetail();

        yield TextField::new('title', 'Titre interne');

        yield TextField::new('slug', 'Slug (URL)')
            ->setHelp('Minuscules, chiffres et tirets uniquement — ex. <code>mentions-legales</code>');

        yield FormField::addFieldset('Référencement (SEO)');

        yield TextField::new('metaTitle', 'Meta title')
            ->setHelp('Titre affiché dans Google (environ 60 caractères).');

        yield TextField::new('metaDescription', 'Meta description')
            ->setHelp('Texte court pour les résultats Google (environ 155 caractères).');

        yield TextField::new('ogImage', 'Image Open Graph (URL ou chemin)')
            ->hideOnIndex();

        yield FormField::addFieldset('Publication');

        yield BooleanField::new('isHomepage', 'Page d\'accueil');

        yield BooleanField::new('isPublished', 'Publiée');

        yield ArrayField::new('footerPayload', 'Bloc pied de page (données structurées)')
            ->onlyOnDetail()
            ->setHelp('Construit automatiquement par les fixtures : marque du header, pied de page, liens réseaux, etc.');

        yield DateTimeField::new('createdAt', 'Créée le')
            ->hideOnForm()
            ->hideOnIndex();

        yield DateTimeField::new('updatedAt', 'Mise à jour')
            ->hideOnForm()
            ->hideOnIndex();
    }
}
