<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setPageTitle('index', 'Administrateurs du site')
            ->setDefaultSort(['email' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->reorder(Crud::PAGE_EDIT, [
                Action::SAVE_AND_CONTINUE,
                Action::SAVE_AND_RETURN,
                Action::DETAIL,
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex()->onlyOnDetail();

        yield TextField::new('email', 'Adresse e-mail')
            ->setHelp('Servira d\'identifiant de connexion (identique au formulaire « Mot de passe oublié » plus tard).');

        yield ChoiceField::new('roles', 'Privilèges')
            ->allowMultipleChoices()
            ->setChoices([
                'Administrateur (accès au back-office)' => 'ROLE_ADMIN',
            ])
            ->setHelp('ROLE_USER est toujours ajouté par Symfony pour les usages sur le site public.')
            ->renderAsBadges();

        yield FormField::addFieldset('Sécurité');

        yield TextField::new('plainPassword', 'Mot de passe')
            ->onlyOnForms()
            ->hideOnDetail()
            ->setFormType(PasswordType::class)
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->setColumns('col-md-6')
            ->setHelp(Crud::PAGE_NEW === $pageName
                ? 'Choisissez un mot de passe robuste.'
                : 'Laisser vide pour conserver le mot de passe actuel.');
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->hashPasswordIfNeeded($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->hashPasswordIfNeeded($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function hashPasswordIfNeeded(object $entity): void
    {
        if (!$entity instanceof User) {
            return;
        }

        $plain = $entity->getPlainPassword();
        if (null === $plain || '' === $plain) {
            return;
        }

        $entity->setPassword($this->passwordHasher->hashPassword($entity, $plain));
        $entity->setPlainPassword(null);
    }
}
