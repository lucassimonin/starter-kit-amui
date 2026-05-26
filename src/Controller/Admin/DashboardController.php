<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->redirectToRoute('admin_page_index');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Starter Kit — Back-office');
    }

    public function configureAssets(): Assets
    {
        // TextEditorType used inside custom ArrayField sub-forms does not get TextEditorField assets; load Trix globally on forms.
        return parent::configureAssets()
            ->addCssFile(Asset::fromEasyAdminAssetPackage('field-text-editor.css')->onlyOnForms())
            ->addJsFile(Asset::fromEasyAdminAssetPackage('field-text-editor.js')->onlyOnForms());
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Contenu');
        yield MenuItem::linkTo(PageCrudController::class, 'Pages & blocs génériques', 'fa fa-file');

        yield MenuItem::section('Messagerie');
        yield MenuItem::linkTo(ContactMessageCrudController::class, 'Messages contact', 'fa fa-envelope');

        yield MenuItem::section('Administration');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-user-shield');

        yield MenuItem::section();
        yield MenuItem::linkToUrl('Retour au site public', 'fa fa-globe', '/');
    }
}
