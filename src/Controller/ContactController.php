<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig');
    }

    #[Route('/contact/send', name: 'app_contact_send', methods: ['POST'])]
    public function send(Request $request, MailerInterface $mailer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$this->isCsrfTokenValid('contact', $data['_token'] ?? '')) {
            return $this->json(['success' => false, 'error' => 'Token invalide.'], 403);
        }

        $name    = trim($data['name'] ?? '');
        $email   = trim($data['email'] ?? '');
        $subject = trim($data['subject'] ?? '');
        $message = trim($data['message'] ?? '');

        if (!$name || !$email || !$subject || !$message) {
            return $this->json(['success' => false, 'error' => 'Champs manquants.'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'error' => 'Email invalide.'], 400);
        }

        $mail = (new Email())
            ->from($email)
            ->to('contact@docly.fr')
            ->replyTo($email)
            ->subject('[Contact] ' . $subject)
            ->text("Nom : $name\nEmail : $email\n\n$message");

        $mailer->send($mail);

        return $this->json(['success' => true]);
    }
}
