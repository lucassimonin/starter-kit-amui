<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Page;
use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PageController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function homepage(PageRepository $pages): Response
    {
        $page = $pages->findOnePublishedHomepage();

        return $page ? $this->renderSite($page) : $this->renderWelcomeSkeleton();
    }

    /**
     * Single-segment URLs for published pages (priority below /admin, /login, etc.).
     */
    #[Route(
        '/{slug}',
        name: 'app_page_show',
        requirements: ['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'],
        priority: -64,
    )]
    public function pageShow(string $slug, PageRepository $pages): Response
    {
        if ($slug === '') {
            return $this->redirectToRoute('app_home');
        }

        $page = $pages->findOnePublishedBySlug($slug);
        if (null === $page) {
            throw $this->createNotFoundException();
        }

        return $this->renderSite($page);
    }

    private function renderSite(Page $page): Response
    {
        return $this->render('site/page_show.html.twig', ['page' => $page]);
    }

    /** Empty database: explain how to seed content without breaking the UX. */
    private function renderWelcomeSkeleton(): Response
    {
        return new Response(<<<'HTML'
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Starter Kit — aucune page d’accueil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: system-ui; max-width: 40rem; margin: 3rem auto; padding: 0 1rem;">
    <h1>Starter Kit</h1>
    <p>Aucune page publiée n’est marquée comme <strong>page d’accueil</strong>.</p>
    <p>Chargez les données de démonstration :</p>
    <pre style="background:#f4f4f5;padding:1rem;">php bin/console doctrine:fixtures:load</pre>
    <p>Puis cochez une page « page d’accueil » dans l’admin ou réutilisez les fixtures fournies.</p>
</body>
</html>
HTML, status: Response::HTTP_OK, headers: ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
