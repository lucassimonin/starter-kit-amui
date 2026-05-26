<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Page;
use App\Enum\TwitterCardKind;
use App\Form\Admin\FooterSiteChromeFormType;
use App\Form\PageBuilder\SectionBlockFormType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
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
            ->setHelp('Référencement, partages sociaux et indexation.');
        yield TextField::new('metaTitle', 'Meta title')
            ->setHelp('Balise &lt;title&gt; et titres des aperçus (environ 60 caractères).');
        yield TextField::new('metaDescription', 'Meta description')
            ->setHelp('Texte pour Google et réseaux (environ 155 caractères).');
        yield TextField::new('metaRobots', 'Robots (indexation)')
            ->hideOnIndex()
            ->setHelp('Laisser vide pour <code>index,follow</code>. Ex.&nbsp;: <code>noindex,nofollow</code>.');
        yield TextareaField::new('canonicalOverride', 'URL canonique (facultatif)')
            ->hideOnIndex()
            ->setHelp(
                'URL absolue <code>https://…</code>, chemin depuis la racine <code>/mentions-legales</code> '
                .'ou slug sans slash. Vide&nbsp;: URL dérivée de la page d’accueil ou du slug.'
            );
        yield ChoiceField::new('ogType', 'Open Graph · type')
            ->setChoices([
                'Site web' => 'website',
                'Article' => 'article',
                'Profil' => 'profile',
                'Produit' => 'product',
            ])
            ->hideOnIndex();
        yield TextField::new('ogSiteName', 'Open Graph · nom du site')
            ->hideOnIndex()
            ->setHelp('Vide&nbsp;: libellé «&nbsp;marque&nbsp;» du pied de page (<code>brand.line</code>).');
        $twitterChoices = [];
        foreach (TwitterCardKind::cases() as $case) {
            $twitterChoices[$case->label()] = $case;
        }
        yield ChoiceField::new('twitterCard', 'Twitter / X · type de carte')
            ->setChoices($twitterChoices)
            ->hideOnIndex()
            ->setHelp('«&nbsp;Grande image&nbsp;» convient si une image OG est renseignée.');
        yield TextField::new('ogImage', 'Image Open Graph')
            ->hideOnIndex()
            ->setHelp('URL HTTPS ou chemin public (<code>/uploads/…</code>, <code>/images/…</code>).');

        yield FormField::addTab('Bandeau & pied', 'fa-solid fa-window-maximize');
        yield Field::new('footerPayload', 'Pied de page')
            ->setFormType(FooterSiteChromeFormType::class)
            ->setFormTypeOption('allow_file_upload', true)
            ->hideOnIndex()
            ->onlyOnForms()
            ->setHelp(
                'Bloc gauche du pied de page en éditeur riche ; liens réseaux ; favicon, '
                .'Apple Touch et couleur de thème pour le navigateur.'
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
