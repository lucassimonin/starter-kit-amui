<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use App\Enum\ContactMessageStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class ContactMessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ContactMessage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Message')
            ->setEntityLabelInPlural('Messages de contact')
            ->setPageTitle('index', 'Messages de contact')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->disable(Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL);
    }

    public function configureFilters(Filters $filters): Filters
    {
        $choices = [];
        foreach (ContactMessageStatus::cases() as $case) {
            $choices[$case->label()] = $case;
        }

        return $filters->add(
            ChoiceFilter::new('status', 'Statut')->setChoices($choices),
        );
    }

    public function configureFields(string $pageName): iterable
    {
        $readOnly = Crud::PAGE_EDIT === $pageName;

        yield IdField::new('id')->onlyOnIndex()->onlyOnDetail();

        yield DateTimeField::new('createdAt', 'Reçu le')
            ->hideWhenCreating()
            ->setDisabled(Crud::PAGE_EDIT === $pageName);

        yield ChoiceField::new('status', 'Statut')
            ->setChoices(array_combine(
                array_map(static fn (ContactMessageStatus $s) => $s->label(), ContactMessageStatus::cases()),
                ContactMessageStatus::cases(),
            ))
            ->renderAsBadges([
                ContactMessageStatus::New->value => 'warning',
                ContactMessageStatus::Read->value => 'success',
                ContactMessageStatus::Archived->value => 'secondary',
            ]);

        yield TextField::new('name', 'Nom')->setDisabled($readOnly);
        yield TextField::new('email', 'E-mail')->setDisabled($readOnly);
        yield TextField::new('phone', 'Téléphone')->hideOnIndex()->setDisabled($readOnly);
        yield TextField::new('subject', 'Sujet')->setDisabled($readOnly);

        yield TextareaField::new('message', 'Message')
            ->hideOnIndex()
            ->setDisabled($readOnly);

        yield TextField::new('ipAddress', 'Adresse IP')->onlyOnDetail();
        yield TextField::new('userAgent', 'Navigateur')->onlyOnDetail();
    }
}
