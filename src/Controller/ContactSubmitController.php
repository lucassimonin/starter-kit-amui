<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Enum\ContactMessageStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ContactSubmitController extends AbstractController
{
    private const CSRF_TOKEN_ID = 'contact_submit';

    #[Route('/contact', name: 'app_contact_submit', methods: ['POST'])]
    public function __invoke(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $tokenValue = $request->request->getString('_csrf_token');
        if (!$this->isCsrfTokenValid(self::CSRF_TOKEN_ID, $tokenValue)) {
            $this->addFlash('danger', 'Session expirée ou jeton invalide. Merci de réessayer.');

            return $this->redirectToRefererSafe($request);
        }

        $message = new ContactMessage();
        $message->setName($request->request->getString('name'));
        $message->setEmail($request->request->getString('email'));
        $subject = $request->request->get('subject');
        $message->setSubject(\is_string($subject) ? $subject : null);
        $message->setMessage($request->request->getString('message'));
        $message->setStatus(ContactMessageStatus::New);
        $message->setIpAddress($request->getClientIp());
        $message->setUserAgent($request->headers->get('User-Agent'));

        $violations = $validator->validate($message);
        if (\count($violations) > 0) {
            foreach ($violations as $violation) {
                $this->addFlash('danger', (string) $violation->getMessage());
            }

            return $this->redirectToRefererSafe($request);
        }

        $em->persist($message);
        $em->flush();

        $this->addFlash('success', 'Merci ! Votre message a bien été envoyé.');

        return $this->redirectToRefererSafe($request);
    }

    /** @return redirect route to home when Referer missing or external */
    private function redirectToRefererSafe(Request $request): Response
    {
        $referer = $request->headers->get('Referer');
        if (\is_string($referer) && str_starts_with($referer, $request->getSchemeAndHttpHost())) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_home');
    }
}
