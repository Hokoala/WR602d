<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PlanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(PlanRepository $planRepository): Response
    {
        $now   = new \DateTime();
        $plans = $planRepository->findBy(['active' => true], ['price' => 'ASC']);

        /** @var User|null $user */
        $user          = $this->getUser();
        $currentPlanId = $user?->getPlan()?->getId();

        $items = array_map(function ($plan) use ($now) {
            $special    = $plan->getSpecialPrice();
            $from       = $plan->getSpecialPriceFrom();
            $to         = $plan->getSpecialPriceTo();
            $hasSpecial = $special !== null
                && ($from === null || $now >= $from)
                && ($to === null || $now <= $to);

            $tools = array_map(fn($t) => $t->getName(), $plan->getTools()->toArray());

            return [
                'id'              => $plan->getId(),
                'name'            => $plan->getName(),
                'description'     => $plan->getDescription(),
                'price'           => $plan->getPrice(),
                'specialPrice'    => $hasSpecial ? $special : null,
                'limitGeneration' => $plan->getLimitGeneration(),
                'tools'           => $tools,
            ];
        }, $plans);

        return $this->render('home/index.html.twig', [
            'plans'         => $items,
            'currentPlanId' => $currentPlanId,
        ]);
    }
}
