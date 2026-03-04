<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\GenerationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class HistoriqueController extends AbstractController
{
    #[Route('/historique', name: 'app_historique')]
    public function index(GenerationRepository $generationRepository): Response
    {
        /** @var User $user */
        $user        = $this->getUser();
        $generations = $generationRepository->findByUser($user);
        $limit       = $user->getPlan()?->getLimitGeneration();
        $used        = count($generations);

        $items = array_map(fn($g) => [
            'id'        => $g->getId(),
            'file'      => $g->getFile(),
            'toolName'  => $g->getToolName(),
            'createdAt' => $g->getCreateadAt()?->format('d/m/Y H:i'),
        ], $generations);

        return $this->render('historique/index.html.twig', [
            'generations'     => $items,
            'generationUsed'  => $used,
            'generationLimit' => $limit,
            'planName'        => $user->getPlan()?->getName() ?? 'FREE',
        ]);
    }
}
