<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
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

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Contenu');
        yield MenuItem::linkTo(PageCrudController::class, 'Pages & SEO', 'fa fa-file');
        yield MenuItem::linkTo(SectionBlockCrudController::class, 'Blocs de sections', 'fa fa-layer-group');

        yield MenuItem::section('Messagerie');
        yield MenuItem::linkTo(ContactMessageCrudController::class, 'Messages contact', 'fa fa-envelope');

        yield MenuItem::section('Administration');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-user-shield');

        yield MenuItem::section();
        yield MenuItem::linkToUrl('Retour au site public', 'fa fa-globe', '/');
    }
}
