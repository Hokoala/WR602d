<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserContact;
use App\Repository\GenerationRepository;
use App\Repository\UserContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/account')]
class ShareController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private GenerationRepository $generationRepository,
        private UserContactRepository $contactRepository,
    ) {}

    #[Route('/contacts', name: 'app_contacts', methods: ['GET', 'POST'])]
    public function contacts(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('add-contact', $request->request->get('_token'))) {
                $this->addFlash('error', 'Token invalide.');
                return $this->redirectToRoute('app_contacts');
            }

            $contact = new UserContact();
            $contact->setFirstname($request->request->get('firstname', ''));
            $contact->setLastname($request->request->get('lastname', ''));
            $contact->setEmail($request->request->get('email', ''));
            $contact->setUser($user);

            $this->em->persist($contact);
            $this->em->flush();

            $this->addFlash('success', 'Contact ajouté.');
            return $this->redirectToRoute('app_contacts');
        }

        $contacts = $this->contactRepository->findBy(['user' => $user], ['id' => 'DESC']);

        return $this->render('share/contacts.html.twig', [
            'contacts'  => $contacts,
            'firstname' => $user->getFirstname(),
            'lastname'  => $user->getLastname(),
            'email'     => $user->getEmail(),
        ]);
    }

    #[Route('/contacts/{id}/delete', name: 'app_contacts_delete', methods: ['POST'])]
    public function deleteContact(int $id, Request $request): Response
    {
        /** @var User $user */
        $user    = $this->getUser();
        $contact = $this->contactRepository->findOneBy(['id' => $id, 'user' => $user]);

        if ($contact && $this->isCsrfTokenValid('delete-contact-' . $id, $request->request->get('_token'))) {
            $this->em->remove($contact);
            $this->em->flush();
        }

        return $this->redirectToRoute('app_contacts');
    }

    #[Route('/share/{id}', name: 'app_share', methods: ['GET', 'POST'])]
    public function share(int $id, Request $request): Response
    {
        /** @var User $user */
        $user       = $this->getUser();
        $generation = $this->generationRepository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$generation) {
            throw $this->createNotFoundException();
        }

        $contacts = $this->contactRepository->findBy(['user' => $user], ['id' => 'DESC']);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('share-pdf', $request->request->get('_token'))) {
                $this->addFlash('error', 'Token invalide.');
                return $this->redirectToRoute('app_share', ['id' => $id]);
            }

            $recipientEmail = $request->request->get('email', '');
            $recipientName  = $request->request->get('name', 'Contact');

            if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Adresse email invalide.');
                return $this->redirectToRoute('app_share', ['id' => $id]);
            }

            $senderName = trim(($user->getFirstname() ?? '') . ' ' . ($user->getLastname() ?? '')) ?: $user->getEmail();
            $toolName   = $generation->getToolName() ?? 'PDF';
            $date       = $generation->getCreateadAt()?->format('d/m/Y à H:i');

            $email = (new Email())
                ->from('noreply@docly.com')
                ->to($recipientEmail)
                ->subject("{$senderName} vous a partagé un PDF — Docly")
                ->text(
                    "Bonjour {$recipientName},\n\n"
                    . "{$senderName} vous a partagé un document PDF généré avec Docly.\n\n"
                    . "Outil utilisé : {$toolName}\n"
                    . "Date de génération : {$date}\n\n"
                    . "L'équipe Docly"
                );

            $filePath = $this->getParameter('kernel.project_dir') . '/var/pdfs/' . $generation->getFile();
            if (file_exists($filePath)) {
                $mimeType = str_ends_with($generation->getFile(), '.zip') ? 'application/zip' : 'application/pdf';
                $email->attachFromPath($filePath, $generation->getFile(), $mimeType);
            }

            try {
                $this->mailer->send($email);
                $this->addFlash('success', "PDF partagé à {$recipientEmail}.");
            } catch (\Throwable) {
                $this->addFlash('error', "Erreur lors de l'envoi.");
            }

            return $this->redirectToRoute('app_share', ['id' => $id]);
        }

        return $this->render('share/share.html.twig', [
            'generation' => $generation,
            'contacts'   => $contacts,
            'firstname'  => $user->getFirstname(),
            'lastname'   => $user->getLastname(),
            'email'      => $user->getEmail(),
        ]);
    }

    #[Route('/history/{id}/download', name: 'app_history_download', methods: ['GET'])]
    public function download(int $id): Response
    {
        /** @var User $user */
        $user       = $this->getUser();
        $generation = $this->generationRepository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$generation) {
            throw $this->createNotFoundException();
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/var/pdfs/' . $generation->getFile();

        if (!file_exists($filePath)) {
            $this->addFlash('error', 'Fichier non disponible.');
            return $this->redirectToRoute('app_account_history');
        }

        $mimeType = str_ends_with($generation->getFile(), '.zip') ? 'application/zip' : 'application/pdf';

        return new Response(file_get_contents($filePath), 200, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $generation->getFile() . '"',
        ]);
    }
}
