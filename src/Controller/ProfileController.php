<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\GenerationRepository;
use App\Service\YourGotenbergService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(GenerationRepository $generationRepository): Response
    {
        /** @var User $user */
        $user  = $this->getUser();
        $limit = $user->getPlan()?->getLimitGeneration();
        $used  = $generationRepository->countByUser($user);

        return $this->render('profile/index.html.twig', [
            'user'            => $user,
            'generationUsed'  => $used,
            'generationLimit' => $limit,
        ]);
    }

    #[Route('/profile/pdf', name: 'app_profile_pdf')]
    public function downloadPdf(YourGotenbergService $pdfService): Response
    {
        $user = $this->getUser();

        $html = $this->renderView('profile/pdf.html.twig', [
            'user' => $user,
        ]);

        $pdfContent = $pdfService->generatePdfFromHtml($html);

        $filename = sprintf('profil-%s-%s.pdf',
            $user->getFirstname() ?? 'user',
            $user->getLastname() ?? $user->getId()
        );

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
